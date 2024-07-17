(function ($, Drupal) {
  'use strict';

  Drupal.behaviors.timerStatus = {
    attach: function (context, settings) {
      $('.timer-status', context).once('timer-status').each(function () {
        var $timer = $(this);
        var $elapsedTime = $timer.find('.timer-status__elapsed-time');
        var startTime = $timer.data('start-time');
        var isRunning = $timer.hasClass('timer-status--running');

        function updateElapsedTime() {
          if (isRunning) {
            var elapsed = Math.floor((Date.now() - startTime) / 1000);
            var hours = Math.floor(elapsed / 3600);
            var minutes = Math.floor((elapsed % 3600) / 60);
            var seconds = elapsed % 60;
            $elapsedTime.text(
              hours.toString().padStart(2, '0') + ':' +
              minutes.toString().padStart(2, '0') + ':' +
              seconds.toString().padStart(2, '0')
            );
          }
        }

        if (isRunning) {
          setInterval(updateElapsedTime, 1000);
        }

        $timer.on('click', function () {
          var url = $timer.data('toggle-url');
          $.post(url, function (response) {
            if (response.status) {
              $timer.toggleClass('timer-status--running timer-status--stopped');
              $timer.find('.timer-status__icon').text(response.status === 'running' ? Drupal.t('Stop') : Drupal.t('Play'));
              isRunning = response.status === 'running';
              if (isRunning) {
                startTime = Date.now();
                setInterval(updateElapsedTime, 1000);
              }
            }
          });
        });
      });
    }
  };

})(jQuery, Drupal);
