<article id="layout"></article>
<script>
    (function () {
        $(document).ready(function(){
            builder.Layout('role',"#layout",{endpoint: '/roles/fetch?id=<?= $this->Request->getParams('GET', 'id') ?>'});
        });
    })();
</script>
