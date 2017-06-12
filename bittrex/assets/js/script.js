$(document).ready(function() {
  
	$('#method').on('change', function(){

		var $this = $(this);

		if($this.val() === 'Buy' || $this.val() === 'Sell'){
			$('.cancelBox').hide();
			$('.otherBox').show();
		}else if($this.val() === 'Cancel'){
			$('.cancelBox').show();
			$('.otherBox').hide();
		}
	})  

  
});