Drupal.behaviors.patroninfoBehavior = function (context) {
   function timeMsg() {
	var t=setTimeout("var h=location.hostname; window.location='http://'+h+'/myacct';",10*60*1000);
        document.cookie = encodeURIComponent('netid')+'=deleted; path=/; domain=.cornell.edu; expires=' + new Date(0).toUTCString();
  }
  timeMsg();
  $(".cite").click(function(event) {
     //alert($(this));
     //alert(event);
     //alert(event.target.nodeName);
     hr = event.target.parentNode.childNodes[1].href;
     //alert(hr);
     ti = event.target.parentNode.nextSibling.nextSibling.firstChild.nodeValue;
     //alert(ti);
     $("#patroninfo-export-box").corner();
     _pi_refbox(hr,ti);
   });
  $("#togglecb").click( function(event) {
    $("#patroninfo-export-box").hide();
    return true;
   });
  $("#cbotton").click(function(event){
    d = $("#renewform").attr("action"); 
    $("#citeall").attr("value","no"); 
    $("#renewform").attr("target","refworks"); 
    $("#renewform").attr("action",d.replace(/renew/,"multiplec2")); 
    return true;
    });

   $("#cbottona").click(function(event){
    d = $("#renewform").attr("action"); 
    $("#citeall").attr("value","yes"); 
    $("#renewform").attr("target","refworks"); 
    $("#renewform").attr("action",d.replace(/renew/,"multiplec2")); 
    return true;
    });
   $("#cbottonc").click(function(event){
    d = $("#renewform").attr("action"); 
    $("#renewform").attr("action",""); 
    $("#renewform").attr("target",""); 
    $("#renewform").attr("action",d.replace(/multiplec2/,"renew")); 
    return true;
    });

   // Dialog
   $('#dialog').dialog({
                autoOpen: false,
                width: 600
   });

   // Dialog Link
   $('.cbox').click(function(){
      $('#dialog').dialog('open');
      return false;
   });
};
function _pi_refbox(d,t){
     $("#patroninfo-export-box").show();
     $("#rname").text(t.substring(0,21));
     $("#dest") .attr(  "href" ,d);
     $("#dest2").attr(  "href" ,d.replace(/citation/,"ris"));
     $("#dest3").attr(  "href" ,d.replace(/citation/,"bt"));
}
