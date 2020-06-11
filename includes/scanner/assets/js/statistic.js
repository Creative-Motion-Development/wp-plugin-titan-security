jQuery(function ($) {

    var chat_html_id = 'wtitan-scan-chart';
    var ctx = document.getElementById(chat_html_id);

    window.wtitan_chart = new Chart(ctx, {
        type: 'doughnut',
        data: {
            datasets: [{
                data: [
                    $('#' + chat_html_id).attr('data-cleaned'),
                    $('#' + chat_html_id).attr('data-suspicious'),
                ],
                backgroundColor: [
                    '#5d05b7',
                    '#f1b1b6',
                ],
                borderWidth: 0,
                label: 'Dataset 1'
            }]
        },
        options: {
            legend: {
                display: false
            },
            events: [],
            animation: {
                easing: 'easeOutBounce'
            },
            responsive: false,
            cutoutPercentage: 80
        }
    });
});
