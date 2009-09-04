
function removeMaintainer(user)
{
  $.post(
    'checkrepo.php',
    {maintainer: user},
    function(data)
    {
      $('#maintainer-' + user + '-li').fadeOut();
      setTimeout(function() {
        $('#maintainer-' + user + '-li').remove();
      }, 3000);
    }
  );
}
