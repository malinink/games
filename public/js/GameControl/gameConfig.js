/**
 *
 * @author Ananaskelly
 */
define(function(){
    var revert = false;
    var current = 0;
    var pawnSpecial = null;
    var figureSize;
    var coeff = 0.4;
    return {
        setRevert: function(value){
            revert = value;
        },
        setCurrent: function(val){
            current = val;
        },
        setPawnSpecial: function(val){
            pawnSpecial = val;
        },
        setFigureSize: function(value){
            figureSize = value;
        },
        getRevert: function(){
            return revert;
        },
        getCurrent: function(){
            return current;
        },
        getPawnSpecial: function(){
            return pawnSpecial;
        },
        getFigureConfig: function(){
            return {
                'size': figureSize,
                'coeff': coeff
            }
        }
    }
})
