function getXmlHttp(){
  var xmlhttp;
  try {
    xmlhttp = new ActiveXObject("Msxml2.XMLHTTP");
  } catch (e) {
    try {
      xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
    } catch (E) {
      xmlhttp = false;
    }
  }
  if (!xmlhttp && typeof XMLHttpRequest!='undefined') {
    xmlhttp = new XMLHttpRequest();
  }
  return xmlhttp;
}


function del(domain){

	if(confirm('Вы уверены, что хотите удалить сайт "' + domain +'"?')){

		var req = getXmlHttp()  

		req.onreadystatechange = function() {  
			if (req.readyState == 4) { 
				if(req.status == 200) { 
					document.getElementById("tr_"+domain).style.display = "none";
				}
			}
		}

		req.open('GET', '?do=delete_site&domain=' + domain, true);  
		req.send(null);
	}	
}

function toggle(el) {
  el.style.display = (el.style.display == 'none') ? '' : 'none'
}

function add_group(){

	var group = prompt('Введите название группы');
    
    if (group != null) {

        var req = getXmlHttp()  

		req.onreadystatechange = function() {  
			if (req.readyState == 4) { 
				if(req.status == 200) { 
					var para = document.createElement("li");
					para.innerHTML = req.responseText;

					var element = document.getElementById("nav_groups");
					var child = document.getElementById("add_group");

					var load = document.getElementById("load");
					element.removeChild(load);

					element.insertBefore(para,child);
				}
			}
		}

		req.open('GET', '?do=add_group&group=' + encodeURIComponent(group), true);  
		req.send(null);

		var para = document.createElement("li");
		para.setAttribute("id", "load");
		para.innerHTML = "<img src=\"styles/load2.gif\">";

		var element = document.getElementById("nav_groups");
		element.appendChild(para);
    }
}


function change_password(){

	var pass = prompt('Введите новый пароль');
    
    if (pass != null) {

        var req = getXmlHttp()  

		req.onreadystatechange = function() {  
			if (req.readyState == 4) { 
				if(req.status == 200) { 
					document.getElementById("change_password").innerHTML = 'Пароль изменен';
				}
			}
		}

		req.open('GET', '?do=change_password&pass=' + encodeURIComponent(pass), true);  
		req.send(null);

		document.getElementById("change_password").innerHTML = '<img src="styles/load2.gif">';
    }
}

function select(source) {
  checkboxes = document.getElementsByName('sites[]');
  for(var i=0, n=checkboxes.length;i<n;i++) {
    checkboxes[i].checked = source.checked;
  }
}

 function do_this(){

        var checkboxes = document.getElementsByName('sites[]');
        var button = document.getElementById('toggle');

        if(button.value == 'Выбрать все'){
            for (var i in checkboxes){
                checkboxes[i].checked = 'FALSE';
            }
            button.value = 'Сбросить'
        }else{
            for (var i in checkboxes){
                checkboxes[i].checked = '';
            }
            button.value = 'Выбрать все';
        }
    }