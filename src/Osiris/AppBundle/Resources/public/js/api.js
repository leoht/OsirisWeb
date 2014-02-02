'use strict'

var socket
var SOCKET_SERVER_ADDR = '127.0.0.1'
var SOCKET_SERVER_PORT = '4567'

function Api () {
	var token = '';
}

Api.beginConnection = function (callback) {
	socket = new WebSocket('ws://'+SOCKET_SERVER_ADDR+':'+SOCKET_SERVER_PORT)

	socket.onmessage = function (message) {
		var data = JSON.parse(message.data)

		console.log(data)

		if (data.name == 'api.associated.token') {
			Api.token = data.data.token
		}
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

	if (Api.token != '') {
		message.token = Api.token
	}

	socket.send(JSON.stringify(message))
};
