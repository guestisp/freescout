/*if (typeof(Vars) == "undefined") {
	Vars = {};
}
var ModuleVars = {
    hello_world: true
};
Vars = $.extend(ModuleVars, Vars);*/


if (typeof(LangMessages) == "undefined") {
	LangMessages = {};
}
	var locale_messages = {
        
        "new_saved_reply": "New Saved Reply",
        "confirm_delete_saved_reply": "Delete this Saved Reply?"
	};

   	if (typeof(LangMessages["en.messages"]) == "undefined") {
		LangMessages["en.messages"] = {};
	}
	LangMessages["en.messages"] = $.extend(locale_messages, LangMessages["en.messages"]);

(function () {
	if (typeof(Lang) == "undefined") {
    	Lang = new Lang();
    }
    Lang.setMessages(LangMessages);
})();