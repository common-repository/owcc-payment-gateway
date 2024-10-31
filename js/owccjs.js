jQuery( document ).ready(function() {
    jQuery('button.copyButton').on('click', function() {
     
     jQuery(this).siblings('input.linkToCopy').select();      
     document.execCommand("copy");
     jQuery(".copied").text("Copied to clipboard").show().fadeOut(1200);
    });
    
    
     jQuery(".owc").css({"display": "block"});
    jQuery('.currency').on('change', function() {
      var cur =  this.value;
      if(cur == 'btc'){
         jQuery(".btc").css({"display": "block"});
         jQuery(".owcc").css({"display": "none"});
         jQuery(".xlm").css({"display": "none"});
          jQuery(".owc").css({"display": "none"});
      }
      else if(cur == 'owcc'){
         jQuery(".owcc").css({"display": "block"});
         jQuery(".btc").css({"display": "none"});
         jQuery(".xlm").css({"display": "none"});
          jQuery(".owc").css({"display": "none"});
      }
      else if(cur == 'xlm'){
         jQuery(".xlm").css({"display": "block"});
         jQuery(".btc").css({"display": "none"});
         jQuery(".owcc").css({"display": "none"});
          jQuery(".owc").css({"display": "none"});
      }
      else{
         jQuery(".owc").css({"display": "block"});
         jQuery(".btc").css({"display": "none"});
         jQuery(".owcc").css({"display": "none"});
         jQuery(".xlm").css({"display": "none"});
      }
      
    });
    
    
});

