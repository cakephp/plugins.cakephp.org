$.getScript('http://arshaw.com/js/fullcalendar-1.6.4/fullcalendar/fullcalendar.min.js',function(){
  
  var date = new Date();
  var d = date.getDate();
  var m = date.getMonth();
  var y = date.getFullYear();
  
  $('#calendar').fullCalendar({
    header: {
      left: 'today',
      center: 'prev,title,next',
      right: ''
    },
    editable: true,
    events: [
      {
        title: "- Hannover \n - Meetup Day \n - PHP London \n - Boston PHP Meetup \n - Atlanta PHP User Groupe \n - Seattle PHP Meetup Group",
        start: new Date(y, m, 3),
        url: 'http://henriquemartins.com.br/sites/cake2/calendar-event.html'
      },
      {
        title: "- Hannover \n - Meetup Day \n - PHP London \n - Boston PHP Meetup \n - Atlanta PHP User Groupe \n - Seattle PHP Meetup Group \n -  SF PHP Meetup \n - Minnesota PHP User Group",
        start: new Date(y, m, 12),
        url: 'http://henriquemartins.com.br/sites/cake2/calendar-event.html'
      },
      {
        title: "- Hannover \n - Meetup Day \n - PHP London \n - Boston PHP Meetup \n - Atlanta PHP User Groupe \n - Seattle PHP Meetup Group \n - SF PHP Meetup \n - Minnesota PHP User Group \n - OrlandoPHP User Group \n - PHPTwente Meetup \n - Vilnius PHP Community Meetup",
        start: new Date(y, m, 13),
        url: 'http://henriquemartins.com.br/sites/cake2/calendar-event.html'
      },
      {
        title: "- Hannover \n - Meetup Day \n - PHP London \n - Boston PHP Meetup \n - Atlanta PHP User Groupe \n - Seattle PHP Meetup Group",
        start: new Date(y, m, d, 10, 0),
        end: new Date(y, m, d, 18, 0),
        allDay: false,
         url: 'http://henriquemartins.com.br/sites/cake2/calendar-event.html'
      },
      {
        title: 'Teste Henrique \n - Meetup Day \n - Meetup Day \n - Meetup Day',
        start: new Date(y, m, d+1, 19, 0),
        end: new Date(y, m, d+1, 22, 30),
        allDay: false,
        url: 'http://henriquemartins.com.br/sites/cake2/calendar-event.html'
      }
    ]
  });
})