'use strict'

var socket
var SOCKET_SERVER_ADDR = '127.0.0.1'
var SOCKET_SERVER_PORT = '4567'

function Api () {

}

Api.beginConnection = function () {
	socket = new WebSocket('ws://'+SOCKET_SERVER_ADDR+':'+SOCKET_SERVER_PORT)

	socket.onmessage = function (message) {
		console.log(message.data)
	};
};

Api.send = function (data) {
	socket.send(JSON.stringify(data))
};
