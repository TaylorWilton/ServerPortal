var weather = new Vue({
    el: '#weather',
    data: {
        forecast: {},
        loading: true
    },
    delimiters: ['${', '}']
});

var anime = new Vue({
    el: '#anime',
    data: {
        airingAnime: {},
        loading: true
    },
    delimiters: ['${', '}']
});
var stats = new Vue({
    el: '#stats',
    data: {
        stats: {},
        loading: true
    },
    delimiters: ['${', '}']
});
var hackernews = new Vue({
    el: '#hackernews',
    data: {
        posts: {},
        loading: true
    },
    delimiters: ['${', '}']
});

var disks = new Vue({
    el: '#disks',
    data: {
        disks: {},
        loading: true
    },
    delimiters: ['${', '}']
});

var bbc = new Vue({
    el: '#bbc-rss',
    data: {
        feed: {},
        loading: true
    },
    delimiters: ['${', '}']
});

sections = [hackernews, weather, anime, bbc];

fetch('getData').then(function (response) {
    return response.json();
}).then(function (json) {
    weather.forecast = json.forecast;
    hackernews.posts = json['hackernews'];
    anime.airingAnime = json.airingAnime.reverse();
    bbc.feed = json['bbc'];

    sections.forEach(function (element) {
        element.loading = false;
    })

}).catch(function (err) {
    console.log(err);
});

fetch('getStats').then(function (response) {
    return response.json();
}).then(function (json) {
    stats.stats = json.stats;
    stats.loading = false;
    disks.disks = json.disks;
    disks.loading = false;
}).catch(function (err) {
    console.log(err);
});


