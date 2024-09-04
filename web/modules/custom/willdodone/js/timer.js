(function ($, Drupal, once) {
  'use strict';

  Drupal.behaviors.timerStatus = {
    attach: function (context, settings) {
      console.log(11211, context)
      $('.flag-timer', context).once('.time').each(function () {
        console.log(1111, $this)
        var $timer = $(this);
        var $elapsedTime = $timer.find('.timer-status__elapsed-time');
        var startTime = $timer.data('start-time');
        var isRunning = $timer.hasClass('timer-status--running');

        if ($flag.hasClass('action-flag')) {
          // Start the timer.
          timer = setInterval(function () {
            initialTime--;
            if (initialTime < 0) {
              clearInterval(timer);
              $flag.text('Time expired');
            } else {
              $flag.text(initialTime + ' sec');
            }
          }, 1000);
        } else if ($flag.hasClass('action-unflag')) {
          // Show the variable time.
          $flag.text(initialTime + ' sec');
        }
      });
    }
  };
})(jQuery, Drupal);