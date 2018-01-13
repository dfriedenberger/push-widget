Widget with push api to send data to it

![widget](widget.png)


# Quick Start

## Clone 
```
git clone https://github.com/dfriedenberger/push-widget
```

## Configure apache
```
<VirtualHost *:80>
    ServerName wetter.frittenburger.de
    DocumentRoot /var/www/push-widget/public
</VirtualHost>
```

## Install
Call your url in browser

![install](install.png)

## Send data from somewhere in the world
### Create Json file 
```
cat data.json
{
    "widget"   : "weather",
	"city"     : "Waldaschaff",
	"temp"     : "-5&deg;C",
	"text"     : "11.01.2018",
	"class"    : "nighttime",
	"icon"     : "night-cloudy",
	"forecast" : [
		{
			"title"   : "Mo, 12.01",
			"temp"    : "-2&deg;C",
			"icon"    : "day-cloudy"
		},
		{
			"title"   : "Di, 13.01",
			"temp"    : "3&deg;C",
			"icon"    : "day-cloudy"
		},
		{
			"title"   : "Mi, 15.01",
			"temp"    : "0&deg;C",
			"icon"    : "day-cloudy"
		}
	]
}
```

### Send it as post request
```
curl -i -X POST --header "Authorization: Bearer d38ec3d5-df03-4b43-8c18-319d58a167e4" 
	--header "Accept:application/json" --header "Content-Type: application/json" 
	-d @data.json  http://wetter.frittenburger.de/api/data
```

# Contact
Dirk Friedenberger, Waldaschaff, Germany

Write me (oder Schreibe mir)
projekte@frittenburger.de

http://www.frittenburger.de 

