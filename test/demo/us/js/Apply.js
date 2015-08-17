
//首页发送留言
function IndexsendApply(src) {
    var stxtAddr = $j("txtAddr").val();
    var stxtContact = $j("txtContact").val();
    var stxtMobileNo = $j("txtTel").val();
    var stxtEmail = $j("txtEmail").val();
    var stxtContent = $j("txtCmtContent").val();
    var verCode = $j("txtVerCode").val();
    var err = "";

    var reg = /^\s*$/;
    
    if (reg.test(stxtContact)) {
        err += "<p>姓名不可为空</p>";
    }
    
    var partten = /(^(([0\+]\d{2,3}-)?(0\d{2,3})-)(\d{7,8})(-(\d{3,}))?$)|(^1[3,5,8]\d{9}$)/    //座机或手机
    //var partten = /(^(([0\+]\d{2,3}-)?(0\d{2,3})-)(\d{7,8})(-(\d{3,}))?$)|(^0{0,1}1[3|5|8][0-9]{9}$)/
    //var partten = /^1[3,5,8]\d{9}$/;    //手机
    if (reg.test(stxtMobileNo)) {
        err += "<p>联系方式不可为空</p>";
    } else if (stxtMobileNo.length > 0) {
        if (!partten.test(stxtMobileNo)) {
            err += "<p>联系方式格式错误</p>";
        }
    }
    var PTN_EMAIL = /\w+([-+.']\w+)*@\w+([-.]\w+)*\.\w+([-.]\w+)*/;
    if (reg.test(stxtEmail)) {
        err += "<p>电子邮箱不可为空</p>";
    } else if (!PTN_EMAIL.test(stxtEmail)) {
        err += "<p>电子邮箱格式错误</p>";
    }
    if (reg.test(stxtAddr)) {
        err += "<p>来自地区不可为空</p>";
    }
    if (reg.test(stxtContent)) {
        err += "<p>留言不可为空</p>";
    }
    if (verCode == undefined || verCode.length == 0) {
        err += "<p>请输入验证码</p>";
    }
    if (err.length > 0) {

        $a(err);

        return;
    }
    showProc(src);
    $.post("/ajax.ashx?action=IndexAddLeaveword&t=" + Math.random(), {
        addr: stxtAddr,
        contact: stxtContact,
        mobileno: stxtMobileNo,
        email: stxtEmail,
        content: stxtContent,
        verCode: verCode

    }, function (msg) {
        var sta = gav(msg, "state");
        var sMsg = gav(msg, "msg");
        if (sta == "1") {
            $a(sMsg, 1);
            emptyText('tbCmtsl');
        } else {
            $a(sMsg);
        }
        showProc(src, false);
    });

}
//清除
function emptyText(cntrId) {
    var jTxts;
    if (cntrId == null) {
        jTxts = $("body").find("input[type=text]");
    } else {
        jTxts = $j(cntrId).find("input[type=text]");
    }
    var jTxtss;
    if (cntrId == null) {
        jTxtss = $("body").find("input[type=password]");
    } else {
        jTxtss = $j(cntrId).find("input[type=password]");
    }
    jTxts.each(function () {
        $(this).attr("value", "");
    });
    jTxtss.each(function () {
        $(this).attr("value", "");
    });
    if (cntrId == null)
        jTxts = $("body").find("textarea");
    else
        jTxts = $j(cntrId).find("textarea");
    jTxts.each(function () {
        $(this).attr("value", "");
    });
}
