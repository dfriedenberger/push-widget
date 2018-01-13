widget with push API to send data to your widget
![widget](https://raw.githubusercontent.com/dfriedenberger/push-widget/master/widget.png)


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
![widget](https://raw.githubusercontent.com/dfriedenberger/push-widget/master/widget.png)

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













#### extracted json saved to file
```
{
  "city": "Waldaschaff",
  "date": "Sunday 07 Jan 2018",
  "temp": "4.0 Celsius"
}
```


