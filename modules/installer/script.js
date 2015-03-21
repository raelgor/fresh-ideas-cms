function Installer(){

  this.init = function(){

    $('button').click(function(){

      $('body').append('<div class="spinner"></div>').find('.log').css({
        opacity: .1,
        pointerEvents: "none"
      });
      var vars = {},data = {};

      $('[data-key]').each(function(i,e){
        vars[ $(e).attr('data-key') ] = $(e).val();
      });

      data = {
        api:     "installer",
        request: "initialize",
        vars:    JSON.stringify(vars)
      }

      function always(){
        $('.log').css({opacity:1,pointerEvents:'all'});
        $('.spinner').remove();
      }

      $('.error').html('');

      $.post(
        'api',
        data,
        function(response){
          try{
            response = JSON.parse(response);

          } catch (error) {
            $('.error').html("Failed to parse response: " + error);
          }
        }
      ).fail(function(response){
        $('.error').html(response);
      }).always(always);

      return false;

    });

  }

}