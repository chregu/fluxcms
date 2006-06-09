function showContent(name, tabname){
                var div = document.getElementById(name);
                var tabdiv = document.getElementById(tabname);
                var status = 0;
                var tmp = tabname;
                if (div.style.display == "none") {
                    status = show(name, tabname);
                } else {
                    status = close(name, tabname);
                }
                return tmp;
            }
            
            function show(name, tabname){
                for(test = 0;test < 5; test++) {
                    alert('div' + test);
                    document.getElementById('div' + test).style.backgroundColor = "#cccccc";
                 }
                
                document.getElementById(name).style.display = "";
                document.getElementById(tabname).style.backgroundColor = "#c2be4a";
                var status = 3;
                return status;
            }
            
            function close(name, tabname){
                document.getElementById(name).style.display = "none";
                document.getElementById(tabname).style.backgroundColor = "#cccccc";
                var status = 0;
                return status;
            }
