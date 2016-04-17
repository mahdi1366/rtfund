<script type="text/javascript">
    //-----------------------------
    //	Programmer	: Fatemipour
    //	Date		: 94.08
    //-----------------------------
    Templates.prototype = {
     TabID : '<?= $_REQUEST["ExtTabID"] ?>',
        address_prefix : "<?= $js_prefix_address ?>",
        get : function(elementID){
            return findChild(this.TabID, elementID);
        }
    }
    
    function Templates(){}
    
</script>

