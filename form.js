// JavaScript funtions for form handling

//------------------------------------------------------------------------------------
//
// Global variables
//

var g_muutettu = false;
var g_ie = navigator.appName.indexOf('Microsoft Internet Explorer') == 0;
var g_ns = navigator.appName.indexOf('Netscape') == 0;

//------------------------------------------------------------------------------------
//
// Link open and popup
//

function call(value) {
  if (g_ns) {
    location.href = value;
  } else {
    location.href(value);
  }
}

function link_popup(value) {
  win = window.open(
    value,
    'infoWindow',
    'scrollbars=yes,menubar=no,status=no,width=400,height=500'
  );
}

function clear_value(obj, retval) {
  obj.value = '';
  return retval;
}

function check_send(value, msg) {
  // was call_uri
  var ret = confirm('Haluatko varmasti l�hett��? lomakkeen: ' + msg + '?');
  if (ret) {
    //alert("nyt se poistetaan sitten!");
    //location.href("?action=delete");
    call(value);
    return true;
  } else {
    return false;
  }
}

//------------------------------------------------------------------------------------

//------------------------------------------------------------------------------------
//
// Modified state handling
//

function m() {
  // modified
  g_muutettu = true;
}

function cm() {
  // check modification
  if (g_muutettu == true) {
    var ret = confirm(
      'Sivulla on muutoksia! Sivun vaihtaminen peruuttaa muutokset. Haluatko varmasti vaihtaa sivun?'
    );
    if (ret) {
      return true;
    } else {
      return false;
    }
  }

  return true; // no changes go on
}

function modified(state) {
  g_muutettu = state;
}
//------------------------------------------------------------------------------------
