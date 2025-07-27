<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>新サーバーのシステムcmsphotoimage</title>
    <style>
        * {
            margin: 0;
            padding: 0;
        }

        li {
            list-style: none;
        }

        .picture-list ul {
            display: flex;
            justify-content: space-around;
        }

        .picture-list {
            border-bottom: 3px solid #ccc;
        }

        .picture-list ul li {
            margin: 10px;
            /* flex: 1 0 15%; */
        }

        .picture-list ul li img {
            width: auto;
            height: auto;
            max-width: 100%;
            max-height: 100%;
            border: 1px solid #ccc;
        }
    </style>
</head>

<body>
    <div>
        <div class="latestMovie-picture">
            <!-- 1 -->
            <div class="picture-list">
                <ul>
                    <li>
                        <span>image_search_kikan3.php</span>
                        <img data-src="https://x.hankyu-travel.com/cms_photo_image/image_search_kikan3.php?p_photo_mno=00146-webbn-51673.jpg"
                            class="lazy">
                    </li>
                    <li>
                        <span>image_search_kikan3.php</span>
                        <img data-src="//x.hankyu-travel.com/cms_photo_image/image_search_kikan3.php?p_photo_mno=00000-BL17_-01137.jpg&v=1.0.1"
                            class="lazy">
                    </li>
                    <li>
                        <span>image_search_kikan5.php</span>
                        <img data-src="//x.hankyu-travel.com/cms_photo_image/image_search_kikan5.php?p_photo_mno=00000-BL17_-01137.jpg"
                            class="lazy">
                    </li>

                </ul>
            </div>

        </div>
    </div>
    </div>


</body>

<script>
    const imgs = document.querySelectorAll("img");
    const observer = new IntersectionObserver(callback);
    function callback(entries) {
        entries.forEach((entry) => {
            if (entry.isIntersecting) {
                const image = entry.target;
                const data_src = image.getAttribute("data-src");
                image.setAttribute("src", data_src);
                observer.unobserve(image);
            }
        });
    }
    imgs.forEach((img) => {
        observer.observe(img);
    });
</script>

</html>