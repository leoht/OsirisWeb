'use strict'

var socket
var SOCKET_SERVER_ADDR = '127.0.0.1'
var SOCKET_SERVER_PORT = '4567'

function Api () {

}

Api.beginConnection = function (callback) {
	socket = new WebSocket('ws://'+SOCKET_SERVER_ADDR+':'+SOCKET_SERVER_PORT)

	socket.onmessage = function (message) {
		console.log(message.data)
	};

	socket.onopen = function () {
		callback.call()
	}
};

Api.send = function (name, data) {
	var message = {
		name: name,
		direction: 'player_to_device',
		data: data
	}

	console.log(JSON.stringify(message))

	socket.send(JSON.stringify(message))
};
