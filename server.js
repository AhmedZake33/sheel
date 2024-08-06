const express = require('express');
const http = require('http');
const { Server } = require('socket.io');
const Pusher = require('pusher');
const bodyParser = require('body-parser');


const app = express();
const server = http.createServer(app);
const io = new Server(server, {
    cors: { origin: "*" }
});
const users = [];

const pusher = new Pusher({
    appId: '1706873',
    key: 'e352c1403f81a822031a',
    secret: 'b865e245ca77cc2549a1',
    cluster: 'eu',
    useTLS: true,
    authEndpoint: 'http://127.0.0.1:8000/pusher/auth',
    
});

app.use(bodyParser.urlencoded({ extended: true }));
app.use(bodyParser.json());

app.post('/pusher/auth', (req, res) => {
    const socketId = req.body.socket_id;
    const channelName = req.body.channel_name;
  
    // Authenticate the user and grant access to the private channel
    const auth = pusher.authenticate(socketId, channelName);
  
    res.send(auth);
  });

io.on('connection', (socket) => {

    socket.on("user_connected",function(user){
        users[user] = socket.id;
        // console.log(users);
        io.emit('updateUserStatus',users);
        // console.log('user ' + user + " connected");
    })

    socket.on('disconnect', () => {
        let i = users.indexOf(socket.id);
        console.log('index')
        console.log(i);
        users.splice(i , 1 , 0);
        io.emit('updateUserStatus',users);
        console.log('disconnect');
        console.log(users);
    });


    socket.on('message', (data) => {
        pusher.trigger('private-user-channel', 'my-event', {
            message: data.message,
        });
    });
});

server.listen(3000, () => {
    console.log('Server is running on http://localhost:3000');
});
