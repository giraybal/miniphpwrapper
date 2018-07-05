<?php

use MPW\MPW;

class CL {
    public function isLoggedIn($auth = null) {
        if (!isset($_SESSION['id'])) return false;
        if ($auth != null) return $auth == $_SESSION['auth'];
        return true;
    }

    public function echoYoutubeIframe($videoHash) {
        echo '<iframe type="text/html"
                      class="youtubeVideoIFrame" width="100%" height="100%"
                      src="https://www.youtube.com/embed/' . $videoHash . '?version=3&loop=1&playlist=' . $videoHash . '&autoplay=1&loop=1&controls=0&rel=0&disablekb=1&fs=0&modestbranding=1&showinfo=0"
                      frameborder="0"></iframe>';
    }

//    public function echoYoutubeVideo($videoHash, $id = null) {
//        echo '<iframe id="' . ($id === null ? $videoHash : $id) . '" class="youtubeVideoIFrame" frameborder="0" allowfullscreen="0" width="100%" height="100%" title="YouTube video player"
//                    src="https://www.youtube.com/embed/' . $videoHash . '?&playlist=' . $videoHash . '&version=3&loop=1&autoplay=1&controls=0&rel=0&disablekb=0&fs=0&modestbranding=1&showinfo=0&hd=1&vq=hd720&enablejsapi=1"></iframe>';
//    }
    public function echoYoutubeVideo($videoHash, $id = null) {
        echo '<div id="' . ($id === null ? $videoHash : $id) . '" class="youtubeVideoIFrame" title=""></div>';
    }

    public function youtubeMute($idArr) {
        ?>
        <script>
            const tag = document.createElement('script');

            tag.src = "https://www.youtube.com/iframe_api";
            const firstScriptTag = document.getElementsByTagName('script')[0];
            firstScriptTag.parentNode.insertBefore(tag, firstScriptTag);

            function setMute(videoId, id) {
                const player = new YT.Player(id, {
                    videoId: videoId,
                    playerVars: {
                        mute: 1,
                        autoplay: 1,
                        controls: 0,
                        showinfo: 0,
                        fs: 0,
                        rel: 0,
                        cc_load_policy: 1,
                        iv_load_policy: 3,
                        autohide: 1
                    },
                    events: {
                        onReady: function (e) {
//                            e.target.setVolume(0);
//                            e.target.mute();
//                            e.target.playVideo();

                            player.mute();
                            player.playVideo();
                        },
                        onStateChange: function (e) {
                            if (e.data === YT.PlayerState.ENDED) player.playVideo();
                        },
                        onError: function (e) {
                            //console.log("YT onError: id: " + id);
                        }
                    }
                });
            }

            function onYouTubeIframeAPIReady() {
                //console.log("YT onYouTubeIframeAPIReady");
                <?php
                foreach ($idArr as $id) {
                    $videoId = str_replace('pc_', '', str_replace('mobile_', '', $id));

                    echo "setMute('" . $videoId . "', '" . $id . "');";
                }
                ?>
            }
        </script>
        <!--        <script src="https://www.youtube.com/iframe_api"></script>-->
        <?php
    }

}
