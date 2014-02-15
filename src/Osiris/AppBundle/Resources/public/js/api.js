'use strict'

var SOCKET_SERVER_ADDR = '127.0.0.1'
var SOCKET_SERVER_PORT = '4567'

var Api = function () {
	this.token = '';
	this.callbacks = new Object();
	this.socket = null
}

Api.prototype.beginConnection = function (callback) {
	this.socket = new WebSocket('ws://'+SOCKET_SERVER_ADDR+':'+SOCKET_SERVER_PORT)

	this.socket.onmessage = function (message) {
		var data = JSON.parse(message.data)

		console.log(data)

		if (data.name == 'api.associated.token') {
			this.token = data.data.token
		} else {
			var cb = this.callbacks[data.name]
			if (cb) {
				cb(data.data)
			}
		}
	}.bind(this);

	this.socket.onopen = function () {
		callback.call()
	}
};

Api.prototype.send = function (name, data) {
	var message = {
		name: name,
		direction: 'player_to_device',
		data: data
	}

	if (this.token != '') {
		message.token = this.token
	}

	this.socket.send(JSON.stringify(message))
};

Api.prototype.on = function (messageName, callback) {
	this.callbacks[messageName] = callback
}