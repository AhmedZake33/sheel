require('./bootstrap');

import Echo from "laravel-echo"

window.Pusher = require('pusher-js');

window.Echo = new Echo({
    broadcaster: 'pusher',
    key: 'pusherKey',
    wsHost: window.location.hostname,
    cluster: process.env.MIX_PUSHER_APP_CLUSTER, 
    wsPort: 6001,
    forceTLS: false,
    disableStats: true,
    // authEndpoint: '/broadcasting/auth',
    // auth: {
    //     headers: {
    //         Authorization: 'Bearer ' + "eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9.eyJhdWQiOiI5OWE3MmRjMC0wZjU3LTQ5NmItYmY1ZS0wN2Y3ZjM0YmMwOGEiLCJqdGkiOiJkNmM2NDRhNDdiMTAwMjcyYzA0ZjYyNmY5NThkNzAzODEyZjk3NmUxOTg0OWIxYmEyNzVmMWZhMmUzNDJmZTFhY2UxMTU3NWM4YmY5ODViNyIsImlhdCI6MTcxMDYyNjM5Ni42ODM1NzQsIm5iZiI6MTcxMDYyNjM5Ni42ODM1NzgsImV4cCI6MTcxMDcxMjc5Ni42NzEyNjYsInN1YiI6IjQ4Iiwic2NvcGVzIjpbXX0.oobIape5kfQSrCD6LC_B_8hbY1mb_0q0L0ZeNn1gmX-okiFb24xGd_MY3iyMqY00c_zK2LyMUw7iD615tMhUDjSuTEAdg-CrNn-YOLOO5oix6To0_2tpNZ1BgsogNcFcoVNCWgCj0BW7c0dV_ZWNTjSH3KEshDnHpefIhI1x8-lM7u5DolLUyilY8mhNhXGvlUc6bkyIucmkbUms4sQseoh6miSq4vQPvpHjCBAl5Fw8i8lVpUhzqUcGmqpgpUPPyrJBRY30m2aCGjAtdiTGwNL5bjbNpPQVsPagyrLHdfzSTlYmJOZ71QJRWcGdkuJ0LReHSGjksUOaISB9-EFOZceNB3gdTWh_1WB_fpesrZIXO_I10i17DDvk13kIzQNr2zOckOU7Suh_vdtBa9OaCOqzjq40XQJcGPYEUul5riyDSF9SBi3Ny13kgPwaWkp5Tlk2-_kOpUKBBwlWwuhBU-WQrU89Voj-AkKoWp_Y1xubbZ9wKS2mZjg-hmsACeF-i1WWnN0RvXUke0FPXVioM8ZaFjTAMGPvY5Y1CBYywksN2L1vVfoTxbdk1ezvZjs0YtepirU9iR4kiSn0cG4IVYSIG2lkODroP9UBvQSV33BorrMpqNxDaoFf0Pu9-s37_oMvDYNF9FCUspEe1GsKPKhtvc5EU7ntrzCfAiQTKZU" // Include your access token for authentication
    //     },
    // },

});



window.Echo.channel('Notifiction').listen('ChatMessageEvent', (e) => {
    console.log(e);
    alert();
});


// private channel

window.Echo.private('privateNotification.'+ 48).listen('NotificationEvent', (e) => {
    console.log(e);
    alert("private notification");
});
