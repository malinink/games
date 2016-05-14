/**
 *
 * @author Ananaskelly
 */
define(function(){
    var revert = false;
    return {
        setRevert : function(value){
            revert = value;
        },
        getRevert : function(){
            return revert;
        }
    }
})

