var PLM = function () {
	
	this.load = function() {
		this.domObject = $("<div id=\"divPLM\">");
		this.pContainer = $("<p id=\"pPLMContainer\">").appendTo(this.domObject);
		this.tblLogin = $("<table id=\"tblPLM\">").appendTo(this.pContainer);
		this.tblRow1 = $("<tr>").appendTo(this.tblLogin);
		this.tblRow2 = $("<tr>").appendTo(this.tblLogin);
		this.tblRow3 = $("<tr>").appendTo(this.tblLogin);
		this.tblRow4 = $("<tr>").appendTo(this.tblLogin);
		this.tblCell11 = $("<td>").appendTo(this.tblRow1);
		this.tblCell12 = $("<td>").appendTo(this.tblRow1);
		this.tblCell21 = $("<td>").appendTo(this.tblRow2);
		this.tblCell22 = $("<td>").appendTo(this.tblRow2);
		this.tblCell31 = $("<td>").appendTo(this.tblRow3);
		this.tblCell32 = $("<td>").appendTo(this.tblRow3);
		this.tblCell41 = $("<td colspan=\"2\">").appendTo(this.tblRow4);
		this.lblUsername = $("<label for=\"txtUsername\">Username</label>").appendTo(this.tblCell11);
		this.txtUsername = $("<input type=\"text\" id=\"txtUsername\" />").appendTo(this.tblCell12);
		this.lblPassword = $("<label for=\"txtPassword\">Password</label>").appendTo(this.tblCell21);
		this.txtPassword = $("<input type=\"password\" id=\"txtPassword\" />").appendTo(this.tblCell22);
		this.lblAuthentication = $("<label for=\"cboAuthentication\">Authenticate With</label>").appendTo(this.tblCell31);
		this.cboAuthentication = $("<select id=\"cboAuthentication\">").appendTo(this.tblCell32);
		this.btnSubmit = $("<p id=\"btnPLMSubmit\">Log In</p>").appendTo(this.tblCell41);
		this.pPleaseWait = $("<p style=\"text-align:center;\">Checking...</p>").hide().appendTo(this.domObject);
		
		$.ajax({
			url:ajaxUrl,
			type:"POST",
			dataType:"json",
			data:{
				command:"GET_AUTHENTICATORS"
			},
			success:function(response) {
				for (var r in response) {
					plm.cboAuthentication.append(
						$("<option value=\"" + response[r].name + "\">" + response[r].displayName + "</option>")
					);
				}
			}
		});
		
		this.txtUsername.keydown(function (evt) {
			if (evt.keyCode == 13)
				plm.txtPassword.focus();
		});
		
		this.txtPassword.keydown(function (evt) {
			if (evt.keyCode == 13)
				plm.submit();
		});
		
		this.btnSubmit.click(this.submit);
		
		this.domObject.appendTo($("body"));
		this.domObject.animate(
			{left:0},
			{duration:300, easing:"linear"}
		);
		
		this.txtUsername.focus();
	};
	
	this.destroy = function() {
		alert("Test");
		this.domObject.remove();
	};
	
	this.submit = function() {
		plm.tblLogin.fadeOut(300, function() {
			plm.pPleaseWait.fadeIn(300)
		});
		
		plm.domObject.animate(
			{height:40},
			{
				duration:300,
				easing:"linear",
				complete:function() {
					$.ajax({
						url:ajaxUrl,
						type:"POST",
						dataType:"json",
						data:{
							command:"AUTHENTICATE",
							username:plm.txtUsername.val(),
							password:plm.txtPassword.val(),
							type:plm.cboAuthentication.val()
						},
						success:function(response) {
							if (!response.success) {
								alert(response.message);
								plm.domObject.animate(
									{height:160},
									{duration:300, easing:"linear"}
								);
								plm.pPleaseWait.fadeOut(300, function() {
									plm.tblLogin.fadeIn(300);
								});
							} else {
								$.ajax({
									url:ajaxUrl,
									type:"POST",
									dataType:"json",
									data:{
										application:"PLM",
										command:"chainload"
									},
									success:{

									},
									error:function(jq, status, error) {
										alert(jq.responseText);
									}
								});
							}
						},
						error:function(jq, status, error) {
							alert(jq.responseText);
						}
					});
				}
			}
		);
		
		
	};
	
	this.load();
	
}

$(document).ready(function() {
	plm = new PLM();
});

var plm;
