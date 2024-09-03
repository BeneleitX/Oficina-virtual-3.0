
<script src="https://core.beneleit.talentonet.com/static/beneleit/beneleit.js"></script>
<iframe id="iframe_beneleit" src="about:blank"></iframe>




<script>
    var usuario = <?php echo $usuario->id; ?>;

    $(document).ready(function()
    {
        loadTokenBeneleit('iframe_beneleit', usuario);

    });    
</script>


