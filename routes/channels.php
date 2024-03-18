<?php

use Illuminate\Support\Facades\Broadcast;
use App\Models\User;
use App\Models\Request;

/*
|--------------------------------------------------------------------------
| Broadcast Channels
|--------------------------------------------------------------------------
|
| Here you may register all of the event broadcasting channels that your
| application supports. The given channel authorization callbacks are
| used to check if an authenticated user can listen to the channel.
|
*/

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('channel.{requestId}', function (User $user, int $requestId) {
   return Request::canAccess($requestId,$user);
});


Broadcast::channel('privateNotification.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});


// Broadcast::channel('private-user-channel-{receiverUserId}', function ($user, $receiverUserId) {
//     return (int) $user->id === (int) $receiverUserId;
// });