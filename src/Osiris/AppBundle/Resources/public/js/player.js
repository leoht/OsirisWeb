'use strict'

var playingTimer;

document.addEventListener('DOMContentLoaded', function () {
    initializeMediaPlayer()
}, false)

var mediaPlayer;

var initializeMediaPlayer = function () {
    mediaPlayer = document.getElementById('player')
    mediaPlayer.controls = false
}

var togglePlayPause = function () {
   var btn = document.getElementById('play-pause-button');
   if (mediaPlayer.paused || mediaPlayer.ended) {
      btn.title = 'Pause';
      btn.innerHTML = 'Pause';
      btn.className = 'pause';
      mediaPlayer.play();

      playingTimer = setInterval(function () {
        api.send('api.playing.current_timecode', {"timecode":Math.ceil(mediaPlayer.currentTime)})
      }, 1000)
   }
   else {
      btn.title = 'Play';
      btn.innerHTML = 'Play';
      btn.className = 'play';
      mediaPlayer.pause();
      clearInterval(playingTimer)
   }
}