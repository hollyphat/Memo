// Initialize your app
var myApp = new Framework7({
    modalTitle: 'Mobile Memorandum',
    material: true,
    pushState : true,
    smartSelectOpenIn: 'picker'
});


//login vars

var user_id = sessionStorage.getItem("user_id");
var username = sessionStorage.getItem("username");
var full_name = sessionStorage.getItem("full_name");
var inbox = sessionStorage.getItem("inbox");
var sent = sessionStorage.getItem("sent");
var read = sessionStorage.getItem("read");

//var url = "http://freelance.in/mobile_memo/api.php";
var url = 'http://app.onlinemedia.com.ng/memo/api.php';

// Export selectors engine
var $$ = Dom7;

// Add view
var mainView = myApp.addView('.view-main', {
    // Because we use fixed-through navbar we can enable dynamic navbar
    dynamicNavbar: true
});

// Callbacks to run specific code for specific pages, for example for About page:
myApp.onPageInit('login-screen-embedded', function (page) {
    $$("#register-form").on('submit',function(e){
        e.preventDefault();
        var usern = $$("#username").val();
        var password = $$("#password").val();
        var f_name = $$("#name").val();
        myApp.showPreloader("Signing up");

        $$.ajax({
           url: url,
            data: {
                'register': '',
                'name': f_name,
                'username' : usern,
                'password' : password
            },
            type: 'POST',
            dataType: 'json',
            crossDomain : true,
            cache: false,
            success:function(f){
                var ok = f.ok;
                if(ok == 1){
                    $$("#username").val('');
                    $$("#name").val('');
                    $$("#password").val('');
                }
                myApp.hidePreloader();

                myApp.addNotification({
                    message : f.msg
                });
            },
            error:function(err){
                myApp.hidePreloader();
                myApp.alert("Network error, try again");
            },
            timeout: 60000
        });
    });
});




myApp.onPageInit('index', function (page) {
    //console.log("okay");
    if(is_login()){
        $$("#home").click();
    }
    $$("#login-form").on('submit',function(e){
        e.preventDefault();
        var usern = $$("#username_login").val();
        var password = $$("#password_login").val();
        myApp.showPreloader("Please Wait...");

        $$.ajax({
            url: url,
            data: {
                'login': '',
                'username' : usern,
                'password' : password
            },
            type: 'POST',
            dataType: 'json',
            crossDomain : true,
            cache: false,
            success:function(f){
                var ok = f.ok;
                if(ok == 1){
                    $$("#username_login").val('');
                    $$("#password_login").val('');


                    var info = f.record;
                    sessionStorage.setItem("username",info['username']);
                    sessionStorage.setItem("user_id",info['user_id']);
                    sessionStorage.setItem("full_name",info['name']);

                    var stats = f.stats;
                    sessionStorage.setItem("inbox",stats['inbox']);
                    sessionStorage.setItem("sent",stats['sent']);
                    sessionStorage.setItem("read",stats['read']);
                    myApp.hidePreloader();
                    $$("#home").click();
                }else {
                    myApp.hidePreloader();

                    myApp.addNotification({
                        message: f.msg
                    });


                }
            },
            error:function(err){
                console.log(err.responseText);
                myApp.hidePreloader();
                myApp.alert("Network error, try again");
            },
            timeout: 60000
        });
    });
}).trigger();


myApp.onPageInit('home', function (page) {
    var username2 = sessionStorage.getItem("username");
    if(username2 == "" || username2 == null){
        window.location = "main.html";
    }

    update_stat();

    $$("#logout").on('click',function(){
        myApp.confirm('Are you sure you want to logout?', function () {
            sessionStorage.removeItem("user_id");
            sessionStorage.removeItem("username");

            window.location = "main.html";
        });
    });

    //console.log(inbox_l);
});

myApp.onPageInit('create2',function(page){



    var username2 = sessionStorage.getItem("username");
    if(username2 == "" || username2 == null){
        window.location = "main.html";
    }


    myApp.showPreloader("Loading users...");
    var a = $$("#the-list");
    var u_id = sessionStorage.getItem("user_id");
    $$("#the-user-id").val(u_id);
    $$.ajax({
        url: url,
        data:{
            'user_list': '',
            'user': u_id
        },
        type: 'GET',
        crossDomain : true,
        cache: false,
        success:function(f){
            a.prepend(f);
            myApp.hidePreloader();
        },
        error:function(err){
            myApp.hidePreloader();
            console.log(err);
            myApp.alert("Network error");
        },
        timeout: 60000

    });

    $$("#sending-form").on('submit',function(e){
        e.preventDefault();



        var checkboxes = document.getElementsByName('cc[]');
        var vals = "";
        for (var i=0, n=checkboxes.length;i<n;i++)
        {
            if (checkboxes[i].checked)
            {
                vals += ","+checkboxes[i].value;
            }
        }
        if (vals) vals = vals.substring(1);


        $$.ajax({
            url: url,
            data: {
                user_id: u_id,
                receiver: $$("#rec"),
                cc: vals,
                send_msg: ''
            },
            type: 'post',
            success: function(f){
                console.log(f);
            },
            error:function(err){
                console.log(err);
            }
        });

    });


});

myApp.onPageInit('create',function(page){
   //image1
    create_img('Y','image1');
});
myApp.onPageInit('create',function(page){



    var username2 = sessionStorage.getItem("username");
    if(username2 == "" || username2 == null){
        window.location = "main.html";
    }


    myApp.showPreloader("Loading users...");
    var a = $$("#the-list");
    var u_id = sessionStorage.getItem("user_id");
    $$("#the-user-id").val(u_id);
    $$.ajax({
        url: url,
        data:{
            'user_list': '',
            'user': u_id
        },
        dataType: 'json',
        type: 'GET',
        crossDomain : true,
        cache: false,
        success:function(f){
            //console.log(f);
            var result = f.result;
            var aa;
            for(var i = 0; i < result.length; i++){
                aa = '<option value="'+result[i]['id']+'">'+result[i]['name']+'</option>';
                //console.log(aa);
                myApp.smartSelectAddOption('.smart-select .smart_1', aa);
                myApp.smartSelectAddOption('.smart-select .smart_2', aa);
            }
            myApp.hidePreloader();


        },
        error:function(err){
            myApp.hidePreloader();
            console.log(err.responseText);
            myApp.alert("Network error");
        },
        timeout: 60000

    });

    $$("#sending-form").on('submit',function(e){
        e.preventDefault();




        var receiver = "";
        $$('select[name="receiver"] option:checked').each(function () {
            receiver = this.value;
        });

        var cc = [];
        $$('select[name="cc"] option:checked').each(function () {
            cc.push(this.value);
        });

        if(receiver == ""){
            myApp.alert("Kindly select a receiver from the list!");
            return false;
        }
        var carbon = "";

        for(var k = 0; k<cc.length; k++){
            carbon += ","+cc[k];
        }

        if (carbon) carbon = carbon.substring(1);


        //console.log(receiver);
        //console.log(cc);

        //return;

        myApp.showPreloader("Sending Message");
        $$.ajax({
            url: url,
            data: {
                user_id: sessionStorage.getItem("user_id"),
                receiver: receiver,
                cc: carbon,
                msg: $$("#memo").val(),
                title: $$("#title").val(),
                send_msg: ''
            },
            type: 'post',
            dataType : 'json',
            success: function(f){
                //console.log(f);
                myApp.hidePreloader();
                myApp.addNotification({
                    message : f.msg
                });
                //myApp.alert(f.msg);

                var stats = f.stats;
                sessionStorage.setItem("inbox",stats['inbox']);
                sessionStorage.setItem("sent",stats['sent']);
                sessionStorage.setItem("read",stats['read']);
            },
            error:function(err){
                console.log(err.responseText);
                myApp.alert("Networ error, try again later!");
            },
            timeout: 60000
        });

    });


});

function is_login(){
    var username_2 = sessionStorage.getItem("username");
    if(username_2 == "" || username_2 == null){
        return false;
    }else{
        return true;
    }
}

function messageCount(type){
    if(type == 0){
        return inbox;
    }else if(type == 1){
        return sent;
    }else if(type == 2){
        return read;
    }
}

function update_stat(){
    var u_id = sessionStorage.getItem("user_id");
    $$.ajax({
        url: url,
        data:{
            'msg_list': '',
            'user': u_id
        },
        type: 'GET',
        crossDomain : true,
        cache: false,
        dataType: 'json',
        success:function(f){
            var stats = f.stats;
            sessionStorage.setItem("inbox",stats['inbox']);
            sessionStorage.setItem("sent",stats['sent']);
            sessionStorage.setItem("read",stats['read']);

            var inbox_l = sessionStorage.getItem("inbox");
            var sent_l = sessionStorage.getItem("sent");
            var read_l = sessionStorage.getItem("read");
            var f_name = sessionStorage.getItem("full_name");
            $$("#inbox-count, .inbox-count").html(inbox_l);
            $$("#read-count, .read-count").html(read_l);
            $$("#sent-count, .sent-count").html(sent_l);
            $$("#full-name, .full-name").html(f_name);
        },
        error:function(err){
            myApp.addNotification({
                message: 'Unable to retrieve message count'
            });
        },
        timeout: 60000

    });

}


function getSelectedChbox() {
    var selchbox = [];        // array that will store the value of selected checkboxes

    // gets all the input tags in frm, and their number
    var inpfields = document.getElementsByTagName('input');
    var nr_inpfields = inpfields.length;

    // traverse the inpfields elements, and adds the value of selected (checked) checkbox in selchbox
    for(var i=0; i<nr_inpfields; i++) {
        if(inpfields[i].type == 'checkbox' && inpfields[i].checked == true) selchbox.push(inpfields[i].value);
    }

    return selchbox;
}