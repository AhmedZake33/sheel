<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Pagination\Paginator;
use Spatie\Permission\Models\Role;
use ZipArchive;
use DB;

use App\Models\Archive;
use App\Models\Archive\ArchiveRole;
use App\Models\Archive\ArchiveUser;
use App\Models\System\System;
use App\Models\User;
use App\Models\Log;


class ArchiveController extends Controller
{
    public $user = null;
    public $successStatus = 200;

    public function __construct()
    {
        $this->middleware('auth')->except('index', 'getByShortName','secureDownload', 'zipFiles','page', 'zipAll', 'export','download','parents','zipArchives');
    }

    public function index(Request $request, Archive $archive = null)
    {

        $user = auth()->user();
        if (empty($user) && $request->token) {
            // $user = User::getUserFromToken($request->token);
            if (empty($user)) {
                return error(System::ERROR_INSUFFICIENT_PRIVILEGES, 401);
            }
        }

        if (empty($archive) || empty($archive->id)) {   
            if (!auth()->user()->hasPermissionTo('admin_archive'))
                abort(401);
            $archive = Archive::root();
        }

        if (!$archive->canAccess($user)) {
            return error(System::ERROR_INSUFFICIENT_PRIVILEGES, 401);
        }

        if ($request->language != null) {
            app()->setLocale($request->language);
        }

        $offset = ($request->offset) ? $request->offset : 0;
        $limit = ($request->limit) ? $request->limit : 10;

        $query = Archive::select('*');

        if ($request->search_text) {
            $textSearch = mb_ereg_replace(" ", "%", getFTS($request->search_text));
            $query->where(\DB::raw("COALESCE(archive.search_text,'')"), "like", "%$textSearch%");
        }

        if ($request->search_type == "1") {
            if ($archive != Archive::root()) {
                $path = ($archive) ? "$archive->path/$archive->title" : "";
                $path = str_replace("/", "%", $path);
                $path = str_replace("\\", "%", $path);
                $query->where('archive.path', 'LIKE', "$path%");
            }
        } else {
            $query->where('archive.parent_id', '=', $archive->id);
        }

        if ($request->type !== null) {
            $query->where('archive.type', '=', $request->type);
        }

        if ($request->content_type !== null) {
            $query->where('archive.content_type', '=', $request->content_type);
        }

        $query->where('related_id', 0);

        $direction = ($request->order_direction) ? $request->order_direction : 'ASC';
        if ($request->order_by == null || $request->order_by == "id") {
            $query->orderBy('archive.order', $direction);
        } else {
            $query->orderBy("archive.$request->order_by", $direction);
        }

        $countQuery = clone $query;

        $count = $countQuery->count();

        if ($limit > 0) {
            $query->offset($offset);
            $query->limit($limit + 1);
        }

        $values = $query->get()->toArray();
        $mainArchive =  $archive;
        $more = ($limit > 0 && count($values) > $limit);
        if ($more) array_pop($values);

        $items = [];
        foreach ($values as $value) {
            $value = (object)$value;
            
            $archive = Archive::find($value->id);
            $value->en_title = $archive->title;
            $en_url = route('secure_download_file',['sid' => $archive->secret()]);
            // $value->url =route('secure_download_file',['sid' => $archive->secret()]);
            $archive = $archive->getLocale($request->language); // get locale 
            $ar_url = route('secure_download_file',['sid' => $archive->secret()]);
            $value->url = ($mainArchive->language == 'en')? $en_url : $ar_url;
            // $value->url = ($mainArchive->language == 'en') ? ($request->language == 'en')?$en_url : $ar_url;
            $value->title = $archive->title;
            $value->date = $archive->created_at;
            $value->sid = $archive->secret();
            $value->sub_title = $archive->sub_title;
            $value->archive_link = $archive->archive_link;
            $value->description = $archive->description;
            $value->size = $archive->size;
            $value->second_id = $archive->id;
            $value->canEdit = $archive->canAccess();

            $users = ArchiveUser::select('users.id', 'users.ar_name')->where('archive_users.archive_id', $value->id)
                ->join('users', 'users.id', '=', 'archive_users.user_id')
                ->get()->toArray();

            $roles = ArchiveRole::
            where('archive_roles.archive_id', $value->id)
                ->join('roles', 'roles.id', '=', 'archive_roles.role_id')
                ->pluck('roles.id');

            $value->users = $users;
            $value->roles = $roles;

            $items[] = $value;
        }

        return response()->json(['items' => $items, 'more' => $more, 'count' => $count], $this->successStatus);
    }

    public function order($id, $position, $neighbour_id)
    {

        $neighbour = Archive::find($neighbour_id);
        $archive = Archive::find($id);

        if (!$archive->canEdit())
            return response('', 401);

        if ($neighbour->parent_id != $archive->parent_id)
            return response('', 500);

        \DB::statement("UPDATE archive SET archive.order = archive.order + 1, updated_at = now() WHERE parent_id = $archive->parent_id AND archive.order > $neighbour->order AND id != $neighbour->id;");

        \DB::statement("UPDATE archive SET archive.order = $neighbour->order + 2, updated_at = now() WHERE parent_id = $archive->parent_id AND archive.order = $neighbour->order AND id > $neighbour->id;");

        if ($position == "before") {
            $archive->order = $neighbour->order;
            $neighbour->order++;
            $archive->save();
            $neighbour->save();
        } else if ($position == "after") {
            $order = $neighbour->order;
            $archive->order = $order + 1;
            $archive->save();
            $neighbour->save();
        }

        $order = 1;
        foreach ($archive->parent->children as $child) {
            $child->order = $order++;
            $child->save();
        }

        return response()->json(['success' => true], $this->successStatus);
    }

    public function get(Archive $archive)
    {

        if (!$archive->canAccess()) {
            return error(System::ERROR_INSUFFICIENT_PRIVILEGES, 401);
        }

        if (!$archive->isPage()) {
            return error(System::ERROR_INVALID_REQUEST, 500);
        }

        return response()->json(['content' => $archive->content()], $this->successStatus);
    }

    public function getByShortName($shortName , Request $request)
    {

        $selected = ['id','title','type','content_type','short_name','created_at','parent_id' , 'related_id' , 'language'];

        $archive = Archive::select($selected)->where('short_name', '=', $shortName)->first();

        $children = [];

        $pages = [];

        if ( !empty($archive->childrenFolders()->get())){
            foreach ( $archive->childrenFolders()->get() as $child ){
                array_push($children , [
                    'parent' => formatOneWebsite($child , $request->language) ,
                    "childrenFolder" => formatWebsite($child->childrenFolders()->get() , $request->language) ,
                    "childrenPage" =>  formatWebsite($child->childrenPages()->get() , $request->language),
                    "childrenFile" =>  formatWebsiteImages($child->childrenFiles()->get(), $request->language) ,
                ]);
            }
        }

        if ( !empty($archive->childrenPages()->get())){
            foreach ( $archive->childrenPages()->get() as $child ){
                array_push($pages , [
                    'page' => formatOneWebsite($child , $request->language),
                    "content" => $child->getLocale("$request->language")->content(),
                    "images" => $child->getPageImages("$request->language"),
                ]);
            }
        }

        if ($archive) {
            return response()->json(['archive' => formatOneWebsite($archive,$request->language) ,
                'children' => $children ,
                'topics' => formatWebsite($archive->brothers()->orderBy('order')->get() , $request->language) ,
                'pages' => $pages ,
                'index' => formatOneWebsite($archive->childrenPages()->where('short_name','index')->first() ,$request->language),
                'childrenFile' => formatWebsiteImages($archive->childrenFiles()->get() ,$request->language) ], $this->successStatus);
        }
        return response()->json(['message' => 'not Found'],System::ERROR_ITEM_NOT_FOUND);
    }


    public function canEdit($archive, $parent)
    {

        if ($archive && $archive->id) {
            return $archive->canEdit();
        } else if ($parent && $parent->id) {
            return $parent->canEdit();
        } else {
            return auth()->user()->hasPermissionTo('admin_archive');
        }

        return true;
    }

    public function save(Request $request, Archive $archive = null)
    {

        if (!$this->canEdit($archive, Archive::find($request->parent_id))) {
            return error(System::ERROR_INSUFFICIENT_PRIVILEGES, 401);
        }

        $oldData = (empty($archive->id)) ? null : $archive->getAttributes();


        if ($request->language != null) {
            app()->setLocale($request->language);
        }

        if ($archive) {
            $validatedData = $request->validate([
                'title' => 'required|max:255',
                'short_name' => 'max:256',
            ]);
        }else{
            $validatedData = $request->validate([
                'title' => 'required|max:255',
                'parent_id' => 'required',
                'short_name' => 'max:256',
            ]);
        }


        if (empty($archive)) {
            $archive = new Archive();
            $archive->id = 0;
            $archive->user_id = auth()->id();
            $archive->title = "" . rand();
            $archive->language = isset($request->language) ? $request->language : "en";
        }


        if (!empty($request->short_name)) {
            $count = Archive::where('short_name', '=', $request->short_name)
                ->where('version', '=', $archive->version)
                ->where('language', '=', $archive->language)
                ->where('parent_id', '=', $archive->parent_id)
                ->where('id', '!=', $archive->id)->count();
            if ($count > 0) {
                return error(System::ERROR_ARCHIVE_SHORT_NAME_EXISTS, 500);
            }
        }
        $archive->fill(empty($request->created_at) ? $request->except('created_at') : $request->all());
        $archive->flags = 0;
        if ($request->visible) $archive->flags |= 0x1;
        if ($request->writable) $archive->flags |= 0x2;
        $archive->archive_link =  $archive->archive_link;
        $archive->apply();
        $archive->rename($request->title);
        $locale = $archive->getLocale();
        $locale->description = $request->description;
        $locale->archive_link =  $archive->archive_link;
        $locale->sub_title = $request->sub_title;
        $locale->save();
        $archive->saveFTS();

        Log::log('ARCHIVE UPDATE', $archive , $archive , $oldData);

        if ($request->users_ids) {
            $users = $request->users_ids;
            $usersData = [];
            foreach ($users as $user) {
                $usersData[] = $user;

            }
            $archive->users()->sync($usersData);
        } else {
            $archive->users()->sync([]);
        }

        if ($request->roles) {
            $roles = $request->roles;
            $rolesData = [];
            foreach ($roles as $role) {
                $rolesData[] = $role;

            }
            $archive->roles()->sync($rolesData);
        } else {
            $archive->roles()->sync([]);
        }

//        if ($request->content !== null) $archive->updatePage($request->content);

        return response()->json(['success' => true], $this->successStatus);
    }

    public function saveContent(Request $request, Archive $archive)
    {

        if (!$this->canEdit($archive, Archive::find($request->parent_id))) {
            return error(System::ERROR_INSUFFICIENT_PRIVILEGES, 401);
        }

        if ($request->language != null) {
            app()->setLocale($request->language);
        }

        Log::log('ARCHIVE UPDATE', $archive , (object)['content' => $archive->content()], (object)['content' => $request->content]);
        $archive->updatePage($request->content);

        return response()->json(['success' => true], $this->successStatus);
    }

    public function checkShortName($archive , $shortName = null)
    {

        $archive = ($archive == 0)? Archive::root() : Archive::find($archive);
        $count = 0;
        if ($shortName) {
            $count = Archive::where('short_name', '=', $shortName)
                ->where('version', '=', $archive->version)
                ->where('language', '=', $archive->language)
                ->where('parent_id', '=', $archive->id)
                ->where('id', '!=', $archive->id)->count();
        }


        return response()->json(['exist' => ($count > 0)], $this->successStatus);
    }

    public function parents(Archive $archive, $locale = 'en' , $root_id = 0)
    {

        $result = [];

        if ($archive->id == null) {
            $archive = Archive::root();
        }

        while (true) {

            if ($archive->id == 0)
                array_unshift($result, (object)['id' => 0, 'title' => 'Archive', 'en_title' => 'Archive' , 'short_name' => '']);
            else
                array_unshift($result, (object)['id' => $archive->id, 'title' => $archive->getLocale($locale)->title, 'en_title' => $archive->title , 'short_name' => $archive->short_name]);

            if ($archive->id == $root_id) break;

            $archive = $archive->parent;
        }

        return response()->json(['parents' => $result], $this->successStatus);
    }

    public function page(Request $request, Archive $archive = null) {

        if ($archive) {
            if ($request->token) {
                $user = User::getUserFromToken($request->token);
                if (empty($user) || !$archive->canDownload($user)) {
                    return error(System::ERROR_INSUFFICIENT_PRIVILEGES, 401);
                }
            }
        } else {
            $value = decryptData($request->sid, env("WEBSITE_SHARED_KEY", NULL));
            $list = explode(",", $value);
            $id = $list[0];
            $archive = Archive::where('id', $id)->first();
        }

        if (!$archive->isPage()) {
            return error(System::ERROR_INVALID_REQUEST, 500);
        }

        return response()->json([
            'content' => $archive->getLocale("$request->language")->content() ,
            'parent' =>$archive->parent,
            'imageFolder' => $archive->getPageImagesFolder() ,
        ], $this->successStatus);


    }


    public function download(Archive $archive)
    {

        if ($archive->isFolder()) {
            return error(System::ERROR_INVALID_REQUEST, 500);
        }

        try {
            return $archive->download();

        } catch (\Throwable $e) {
            return error(System::ERROR_OPERATION_FAILED, 500);
        }
    }


    public function secureDownload(Request $request){

        try {
            $value = decryptData($request->sid, env("WEBSITE_SHARED_KEY", NULL));
            $list = explode(",", $value);
            $id = $list[0];

            $archive = Archive::where('id', $id)->first();
            return $archive->download();
        } catch (\Throwable $e) {
            abort(404);
        }
    }

    public function export(Request $request, $archive)
    {

        $archive = $archive?Archive::find($archive): Archive::root();

        $user = User::getUserFromToken($request->token);
        if (!can('admin_archive', $user)) {
            return error(System::ERROR_INSUFFICIENT_PRIVILEGES, 401);
        }

        if (!$archive->isFolder()) {
            return error(System::ERROR_INVALID_REQUEST, 500);
        }

        try {
            return $archive->export();
        } catch (\Throwable $e) {
            return error(System::ERROR_OPERATION_FAILED, 500);
        }
    }


    public function import(Request $request, $archive)
    {
        $archive = $archive? Archive::find($archive) : Archive::root();


        if (!can('admin_archive')) {
            return error(System::ERROR_INSUFFICIENT_PRIVILEGES, 401);
        }

        if (!$archive->isFolder()) {
            return error(System::ERROR_INVALID_REQUEST, 500);
        }

        try {

            $path = $_FILES['file']['tmp_name'];
            $archive->import($path);
            return response()->json(['success' => true], $this->successStatus);

        } catch (\Throwable $e) {
            return error(System::ERROR_OPERATION_FAILED, 500);
        }
    }

    public function delete(Archive $archive)
    {

        if (!$archive->canDelete()) {
            return error(System::ERROR_INSUFFICIENT_PRIVILEGES, 401);
        }

        ArchiveUser::where('archive_id', $archive->id)->delete();
        ArchiveRole::where( 'archive_id', $archive->id)->delete();

        $archive->delete();
        Log::log('ARCHIVE DELETE', $archive , $archive , null);

        return response()->json(['success' => true], $this->successStatus);
    }

    public function updateFile(Archive $archive, Request $request)
    {
        $filesInfos = [];

        $archive->updateFile($request->file('file'));

        $filesInfos[] = (object)[
            'id' => $archive->id,
            'name' => $archive->name(),
        ];
        Log::log('ARCHIVE UPDATE FILE', $archive , $archive , null);
        return response()->json(['success' => true, "files" => $filesInfos], $this->successStatus);
    }

    public function uploadFiles($archive , Request $request )
    {

        $archive = ($archive == 0)? Archive::root() : Archive::find($archive);


        if (!$this->canEdit($archive, Archive::find($request->parent_id))) {
            return error(System::ERROR_INSUFFICIENT_PRIVILEGES, 401);
        }


        if (empty($archive)) {
            $archive = new Archive();
            $archive->id = 0;
        }

        $order = Archive::select('order')->where('parent_id', '=', $archive->id)->max('order');

        $files = $request->file('files');
        $file = $request->file('file');
        if (empty($files) && !empty($file)) {
            $files = [$file];
        }

        $filesInfos = [];

        if (!empty($files)) {
            foreach ($files as $file) {
                if (!empty($request->folder)) {
                    $path = $archive->calculatePath() . '/' . $request->folder;
                    $archive = Archive::get($path);
                }

                $oldFile = null;
                if (!empty($request->title) && $request->replace == "yes") {
                    $oldFile = $archive->findChildByTitle($request->title);
                }
                $order = $order+1;
                $archiveFile = $archive->addFile($file , $request->content_type , $order );
                if ($archiveFile) {
                    if ($oldFile) {
                        Log::log("AUTODAL ARCHIVE", $oldFile, ['action' => 'autodel']);
                        $oldFile->delete();
                    }
                    if (!empty($request->title)) {
                        $archiveFile->rename($request->title);
                    }
                    Log::log("UPLOAD ARCHIVE ", $archiveFile, ['action' => 'done']);
                } else {
                    Log::log("UPLOAD FAILD", $archiveFile, ['action' => 'failed']);
                }

                $filesInfos[] = (object)[
                    'id' => $archiveFile->id,
                    'name' => $archiveFile->name(),
                ];
            }
        }

        return response()->json(['success' => true, 'files' => $filesInfos], $this->successStatus);
    }

    public function pasteTo($archive_id, Request $request)
    {

        // if (!can('edit_archive')) {
        //     return error(System::ERROR_INSUFFICIENT_PRIVILEGES, 401);
        // }

        $archive = $archive_id == 0 ?  Archive::root() : Archive::find($archive_id) ;

        if(!$archive->canAccess()){
            return error(System::ERROR_INSUFFICIENT_PRIVILEGES, 401);
        }

        if (!$archive->isFolder()) {
            return error(System::ERROR_INVALID_REQUEST, 500);
        }


        foreach ($request->marked_ids as $id) {
            $markedArchive = Archive::find($id);
            $markedArchive->copy($archive->id);
        }
        Log::log("ARCHIVE PASTED", Archive::find($archive_id), $request->marked_ids,null);
        return response()->json(['success' => true], $this->successStatus);
    }

    public function moveTo($archive_id, Request $request)
    {

        // if (!can('edit_archive')) {
        //     return error(System::ERROR_INSUFFICIENT_PRIVILEGES, 401);
        // }

        $archive = $archive_id == 0 ?  Archive::root() : Archive::find($archive_id) ;

        if(!$archive->canAccess()){
            return error(System::ERROR_INSUFFICIENT_PRIVILEGES, 401);
        }

        if (!$archive->isFolder()) {
            return error(System::ERROR_INVALID_REQUEST, 500);
        }

        foreach ($request->marked_ids as $id) {
            $markedArchive = Archive::find($id);
            $markedArchive->move($archive->id);
        }
        Log::log("ARCHIVE MOVED", Archive::find($archive_id), $request->marked_ids,null);
        return response()->json(['success' => true], $this->successStatus);
    }

    public function getImages(Archive $archive)
    {

        $data = [];
        $imagesFolder = $archive->parent->findChildByContentType("images");
        if ($imagesFolder) {
            $files = $imagesFolder->subChildren(Archive::TYPE_FILE);
            foreach ($files as $file) {
                $data[] = [
                    "image" => route('secure_download_file', ['sid' => $file->secret()]),
                    "folder" => $file->parent->locale->title,
                ];
            }
        }

        return response()->json($data);
    }

    public function zipFiles(Request $request, $archive)
    {

        $archive = ($archive == 0)?Archive::root() : Archive::find($archive) ;


        if (!$archive->isFolder()) {
            return error(System::ERROR_INVALID_REQUEST, 500);
        }


        $user = User::getUserFromToken($request->token);
        if (empty($user) || !$archive->canDownload($user)) {
            return error(System::ERROR_INSUFFICIENT_PRIVILEGES, 401);
        }

        $zipPath = "/temp_folders/" . ucfirst($archive->title) . ".zip";
        Storage::disk('local')->delete($zipPath);
        $zipPath = storage_path('app') . $zipPath;

        try {

            $zip = new ZipArchive();
            if (!$zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE)) {
                return error(System::ERROR_INSUFFICIENT_PRIVILEGES, 401);
            }

            $files = $archive->children;

            foreach ($files as $file) {
                if ($file->isPage() || $file->isFile()) {
                    $destPath = "$file->title.$file->extension";
                    $zip->addFile($file->physicalPath(), $destPath);
                }
            }

            $zip->close();
            return response()->download(
                $zipPath,
                "$archive->title.zip",
                array('Content-Type: ' . mime_content_type($zipPath)));

        } catch (\Throwable $th) {
            return error(System::ERROR_OPERATION_FAILED, 500);
        }
    }

    public function zipAll(Request $request ,  $archive)
    {

        $archive = ($archive == 0)? Archive::root() : Archive::find($archive);

        if (!$archive->isFolder()) {
            return error(System::ERROR_INVALID_REQUEST, 500);
        }

        $user = User::getUserFromToken($request->token);
        if (empty($user) || !$archive->canDownload($user)) {
            return error(System::ERROR_INSUFFICIENT_PRIVILEGES, 401);
        }

        $zipPath = "/temp_folders/" . ucfirst($archive->title) . ".zip";
        Storage::disk('local')->delete($zipPath);
        $zipPath = storage_path('app') . $zipPath;

        try {
            $zip = new ZipArchive();
            $zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE);

            $path = $archive->physicalPath();
            $files = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($path));

            foreach ($files as $name => $file) {
                if (!$file->isDir()) {
                    $filePath = $file->getRealPath();

                    $relativePath = '' . substr($filePath, strlen($path) + 1);

                    $zip->addFile($filePath, $relativePath);
                }
            }

            $zip->close();
            return response()->download($zipPath);

        } catch (\Throwable $th) {
            return error(System::ERROR_OPERATION_FAILED, 500);
        }
    }

    public function names(Request $request){

        $offset = ($request->offset) ? $request->offset : 0;
        $limit = ($request->limit) ? $request->limit : 10;


        $query = User::select('id', 'ar_name')->leftJoin('model_has_roles', 'model_has_roles.model_id', '=', 'users.id')->distinct('users.id');

        if ($request->search_text) {
            $textSearch = mb_ereg_replace(" ", "%", getFTS($request->search_text));
            $query->where(\DB::raw("CONCAT(COALESCE(users.search_text,''), ' ')"), "like", "%$textSearch%");
        }

        if (!can('all_users')){
            $query = $query->whereIn('model_has_roles.role_id', User::userAccessRole());
        }

        if ($limit > 0) {
            $query->offset($offset);
            $query->limit($limit + 1);
        }

        $users = $query->where('removed',0)->get()->toArray();

        $roles = Role::select('id', 'name')->get()->toArray();

        return response()->json(['roles' => $roles , 'users' => $users], $this->successStatus);
    }

    public function zipArchives(Archive $archive){

        return $archive->export();
    }
}
