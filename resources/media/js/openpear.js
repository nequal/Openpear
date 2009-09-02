//var $;

$(function(){
  $("#source-tree").hover(
    function()
    {
      $("#source-subtree").show();
    },
    function()
    {
      $("#source-subtree").hide();
    }
  );


  //prettyPrint();
});

