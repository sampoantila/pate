// JavaScript funtions for pate

function countmods(person_id, daysinmonth, modules, freemodules) {
  var count = 0;
  var freecount = 0;

  counter = document.getElementById('counter_' + person_id);

  for (row = 0; row < 3; row++) {
    for (i = 1; i <= daysinmonth; i++) {
      field = document.getElementById('mod_' + person_id + '_' + row + '_' + i);

      if (
        field.value.toUpperCase() != 'V' &&
        field.value != 'v' &&
        field.value != ''
      )
        count++;
    }
  }

  counter.value = count - modules;

  if (counter.value > 0) counter.value = '+' + counter.value;

  freecounter = document.getElementById('freecounter_' + person_id);

  for (row = 0; row < 3; row++) {
    for (i = 1; i <= daysinmonth; i++) {
      field = document.getElementById('mod_' + person_id + '_' + row + '_' + i);

      if (field.value.toUpperCase() == 'V' && field.value == 'v') freecount++;
    }
  }
  freecounter.value = freecount - freemodules;
  if (freecounter.value > 0) freecounter.value = '+' + freecounter.value;
}

//------------------------------------------------------------------------------------
