var n=0;

function y_clear(){
	if(n==0)
	{
		document.getElementById("photo_sc_contents_next").style.display = "none";
		document.getElementById("photo_sc_contents_back").style.display = "none";
		n=1;
	} else {
		document.getElementById("photo_sc_contents_next").style.display = "block";
		document.getElementById("photo_sc_contents_back").style.display = "block";
		n=0;
	}
}


