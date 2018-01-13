$( document ).ready(function() {

	$.getJSON( "api/data", function( msg ) {
		
		if(msg.state != "ok")
		{
			alert( msg.state );
			alert( msg.message );
			return;
		}
		
		if(msg.data.widget == "weather")
		{
			//https://github.com/erikflowers/weather-icons
		
			$('#now-city').text(msg.data.city);
			$('#now-text').text(msg.data.text);
			
			$('#now').removeClass();
			$('#now').addClass(msg.data.class);

			$('#now-icon').removeClass();
			$('#now-icon').addClass('wi wi-'+msg.data.icon);
			$('#now-temp').html(msg.data.temp);

			for(var i = 0;i < 3;i++)
			{
				$('#forecast-'+(i+1)+'-title').text(msg.data.forecast[i].title);			
				$('#forecast-'+(i+1)+'-icon').removeClass();
				$('#forecast-'+(i+1)+'-icon').addClass('wi wi-'+msg.data.forecast[i].icon);
				$('#forecast-'+(i+1)+'-temp').html(msg.data.forecast[i].temp);			
			}
		}
	
	});


    
	
});

