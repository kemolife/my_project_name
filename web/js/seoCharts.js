// Load the Visualization API and the corechart package.
google.charts.load('current', {'packages': ['corechart']});

// Set a callback to run when the Google Visualization API is loaded.
google.charts.setOnLoadCallback(dataChartCountry);
google.charts.setOnLoadCallback(dataChartSources);
google.charts.setOnLoadCallback(dataChartOrganic);

// Callback that creates and populates a data table,
// instantiates the pie chart, passes in the data and
// draws it.
function dataChartCountry() {
    var chartsElements = document.getElementsByClassName('country-chart');
    for (var i = 0; i < chartsElements.length; i++) {
        var rows = [];
        var data = new google.visualization.DataTable();
        data.addColumn('string', 'Country');
        data.addColumn('number', 'Percentage');
        var countries = JSON.parse(chartsElements[i].dataset.rows);
        countries.forEach(function (item) {
            rows.push([item.country, item.value]);
        });
        data.addRows(rows);

        var options = {
            'title': 'Countries',
            'width': 400,
            'height': 300
        };

        var chart = new google.visualization.PieChart(chartsElements[i]);
        chart.draw(data, options);
    }
}

function dataChartSources() {

    var chartsElements = document.getElementsByClassName('sources-chart');
    for (var i = 0; i < chartsElements.length; i++) {
        var data = new google.visualization.DataTable();
        data.addColumn('string', 'Country');
        data.addColumn('number', 'Percentage');
        var sources = JSON.parse(chartsElements[i].dataset.rows);
        var rows = [
            ['Appstore', sources.appstore.value],
            ['Direct', sources.direct.value],
            ['Mail', sources.mail.value],
            ['Referral', sources.referral.value],
            ['Referral ad', sources.referral_ad.value],
            ['Search ad', sources.search_ad.value],
            ['Search organic', sources.search_organic.value],
            ['Social', sources.social.value]
        ];


        data.addRows(rows);

        var options = {
            'title': 'Sources',
            'width': 400,
            'height': 300
        };

        var chart = new google.visualization.PieChart(chartsElements[i]);
        chart.draw(data, options);
    }
}

function dataChartOrganic() {
    var chartsElements = document.getElementsByClassName('organic-bar');
    for (var i = 0; i < chartsElements.length; i++) {
        var rows = [];

        var data = new google.visualization.DataTable();
        data.addColumn('string', 'Country');
        data.addColumn('number', 'Percent');
        var organic = JSON.parse(chartsElements[i].dataset.rows);
        organic.forEach(function (value) {
            rows.push([value.keyword, value.percent]);
        });
        data.addRows(rows);
        var options = {
            'title': 'Organic keywords',
            'width': 400,
            'height': 300
            // 'hAxis': {
            //     textPosition: 'none'
            // },
            // 'vAxis': {
            //     textPosition: 'none'
            // },
        };

        var chart = new google.visualization.ColumnChart(chartsElements[i]);
        chart.draw(data, options);
    }
}