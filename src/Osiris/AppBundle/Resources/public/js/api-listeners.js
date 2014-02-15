'use strict';

var api = new Api()

api.on(ASSOCIATION_INITIATED_WITH_CODE, function (data) {
    console.log('Received code : '+data.code)
})

api.on(PLAY, function (data) {
    togglePlayPause()
})

api.on(PAUSE, function (data) {
    togglePlayPause()
})

api.on(SET_VOLUME, function (data) {
    mediaPlayer.volume = data.volume;
})