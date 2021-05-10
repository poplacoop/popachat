
function exportXls(){
        console.log("function de function.js");
        vean=$("#eanSearch").val();
        vdesign=$("#designationSearch").val();
        vrefFour=$("#refFourSearch").val();
        vfournisseur=$("#fournisseur").val();
        vfamille=$("#famille").val();
        vdepartement=$("#departement").val();
        if (vfamille==""){familleStr="";}else{familleStr=" AND famille='"+vfamille+"'";}
        if (vfournisseur==""){fournisseurStr="";}else{fournisseurStr=" AND fournisseur='"+vfournisseur+"'";}
        if (vdepartement==""){departementStr="";}else{departementStr=" AND departement='"+vdepartement+"'";}
        console.log(vdepartement.value);
        //alert(fournisseur);
        query="SELECT * FROM (SELECT * FROM prod_articles where validated<2) as prod_articles WHERE ean like '%"+vean+"%' "+departementStr+" AND designation like '%"+vdesign.toUpperCase()+"%' "+familleStr+"  AND refFour like '%"+vrefFour+"%' "+fournisseurStr+" ORDER BY designation;";
        query.replace(/'/g, "\\'");
        console.log("0017-stockExcel.php?query=\'"+query+"\'");
        $("#exportXls").attr("href","0017-stockExcel.php?query='"+query+"'");
        //$("#exportXls").attr("href","");
        
    }

