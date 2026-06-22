let player;
let currentVideoId = null;

// Загрузка YouTube IFrame API
const tag = document.createElement('script');
tag.src = "https://www.youtube.com/iframe_api";
const firstScriptTag = document.getElementsByTagName('script')[0];
firstScriptTag.parentNode.insertBefore(tag, firstScriptTag);

function onYouTubeIframeAPIReady() {
    player = new YT.Player('player', {
        height: '360',
        width: '640',
        videoId: '',
        playerVars: {
            'autoplay': 0,
            'controls': 1,
            'rel': 0
        },
        events: {
            'onStateChange': onPlayerStateChange
        }
    });
}

function onPlayerStateChange(event) {
    if (event.data === YT.PlayerState.ENDED) {
        console.log('Video ended');
    }
}

// Извлечь videoId из YouTube URL
function extractVideoId(url) {
    if (!url) return null;
    
    const patterns = [
        /(?:youtube\.com\/watch\?v=|youtu\.be\/|youtube\.com\/embed\/)([^&\s?]+)/,
        /^([a-zA-Z0-9_-]{11})$/ // прямой videoId
    ];
    
    for (let pattern of patterns) {
        const match = url.match(pattern);
        if (match && match[1]) {
            return match[1];
        }
    }
    
    return null;
}

// Запуск трека
function playTrack(videoId) {
    const id = extractVideoId(videoId);
    
    if (id && player && player.loadVideoById) {
        if (id !== currentVideoId) {
            player.loadVideoById(id);
            currentVideoId = id;
            console.log('Playing:', id);
        }
    } else {
        console.error('Invalid video ID:', videoId);
    }
}

window.playTrack = playTrack;
window.extractVideoId = extractVideoId;
