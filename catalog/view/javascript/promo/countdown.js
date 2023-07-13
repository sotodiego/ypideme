function countdownTimer(duration, rand, countdown_format) {
  var minutes, seconds, minute, hours, hour, days;
  seconds = parseInt(duration % 60, 10);
  minutes = parseInt(duration / 60, 10);
  minute = parseInt(minutes % 60, 10);
  hours = parseInt(minutes / 60, 10);
  hour = parseInt(hours % 24, 10);
  days = parseInt(hours / 24, 10);

  countdown_format = countdown_format.replace('{days}', days);

  countdown_format = countdown_format.replace("{hours}", (hour < 10) ? ('0' + hour) : hour);
  countdown_format = countdown_format.replace("{minutes}", (minute < 10) ? ('0' + minute) : minute);
  countdown_format = countdown_format.replace("{seconds}", (seconds < 10) ? ('0' + seconds) : seconds);

  $('#clock' + rand).html(countdown_format);
}
