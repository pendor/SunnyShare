
function htmlEntities(str) {
  return str.replace(/[\u00A0-\u9999<>\&]/gim, function(i) {
     return '&#'+i.charCodeAt(0)+';';
  });
}

function getCookie(cname) {
  var name = cname + "=";
  var ca = document.cookie.split(';');
  for(var i = 0; i < ca.length; i++) {
    var c = ca[i];
    while(c.charAt(0) == ' ') {
      c = c.substring(1);
    }
    if (c.indexOf(name) == 0) {
      return c.substring(name.length,c.length);
    }
  }
  return '';
} 

