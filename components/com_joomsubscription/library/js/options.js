window.options_main = {
    chart:       {
        type: 'areaspline'
    },
    title:       {
        text: null
    },
    tooltip:     {
        crosshairs: [true]
    },
    xAxis:       {
        type:   'datetime',
        title:  {
            text: null
        },
        offset: 10
    },
    yAxis:       [
        {
            title:         {
                text:  null,
                style: {
                    color: '#2e95b9'
                }
            },
            labels:        {
                style:     {
                    color: '#2e95b9'
                },
                formatter: function() {
                    return this.value;
                }
            },
            gridLineColor: '#EEEEEE'
        }
    ],
    legend:      {
        enabled: false
    },
    plotOptions: {
        areaspline: {
            fillOpacity: 0.2
        }
    },
    series:      [
        {
            yAxis:         0,
            pointInterval: 24 * 3600 * 1000,
            pointStart:    new Date().getTime() - (24 * 3600 * 29 * 1000),
            color:         '#2e95b9',
            data:          []
        }
    ]
};

window.options_pie = {
    chart:       {
        type: 'pie'
    },
    title:       {
        text: null
    },
    yAxis:       {
        title: {
            text: null
        }
    },
    legend:      {
        enabled: false
    },
    tooltip:     {
        formatter: null
    },
    plotOptions: {
        pie: {
            shadow:           false,
            center:           ['50%', '50%'],
            allowPointSelect: true,
            cursor:           'pointer',
            dataLabels:       {
                enabled: true
            }
        }
    },
    series:      [
        {
            name:        null,
            data:        null,
            size:        '35%',
            borderWidth: 0,
            showInLegend: false,
            dataLabels:  {
                formatter: function() {
                    return this.y > 5 ? this.point.name : null;
                },
                color:     'white',
                distance:  -30
            }
        },
        {
            name:        null,
            data:        null,
            size:        '50%',
            innerSize:   '40%',
            borderWidth: 0,
            showInLegend: true,
            dataLabels:  {
                distance:  30,
                formatter: function() {
                    return this.y > 1 ? '<b>' + this.point.name + ':</b><br> ' + this.y.toFixed(2) + '% (' + this.point.count + '$)' : null;
                }
            }
        }
    ]
};