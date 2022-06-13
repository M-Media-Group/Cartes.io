<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <title>AR Demo</title>
    <script src="https://aframe.io/releases/1.0.4/aframe.min.js"></script>
    <script src="https://unpkg.com/aframe-look-at-component@0.8.0/dist/aframe-look-at-component.min.js"></script>
    <script src="https://raw.githack.com/AR-js-org/AR.js/master/aframe/build/aframe-ar-nft.js"></script>
</head>

<body style="margin: 0; overflow: hidden;">
    <a-scene vr-mode-ui="enabled: false" embedded arjs="sourceType: webcam; videoTexture: true; debugUIEnabled: false;">
        @foreach ($map->markers as $marker)
            <a-text value="{{ $marker->category->name }}" look-at="[gps-camera]" scale="70 70 70"
                gps-entity-place="latitude: {{ $marker->x }}; longitude: {{ $marker->y, 6 }};">
            </a-text>
        @endforeach
        <a-camera gps-camera rotation-reader>
        </a-camera>
    </a-scene>
</body>

</html>
