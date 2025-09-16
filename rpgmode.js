let stats = [];
stats["SCORE"] = 0;
stats["CHECKPOINT"] = 1;
// let variables = [];
let symbols = new RegExp(/[\+\-=\/\*]/)

$("body").append(`<div class='rpg'>
    <div id='stat-SCORE'><h2>SCORE: <span id='SCORE-counter'></span></h2></div></div>`);

function checkScore(text, container) {
    let matches = text.match(/f?[A-Z]+([\+\-=\/\*][0-9\w]+(\.[0-9]+)?)+/g);
    // let wordMatches = text.match(/f?[A-Z]+=[A-Za-z_]+/g);
    let checkpoint = text.includes("CHECKPOINT");

    if (checkpoint) {
        let saveArea = $("#currentArea").val();
        stats["CHECKPOINT"] = saveArea;
        console.log("Checkpoint set to " + saveArea);
    }

    if (matches) {
        for (i=0; i<matches.length; i++) {
            console.log("matches:" , matches)
            that = matches[i];
            let calc = that.match(/[\+\-=\/\*]/g)
            let value = that.match(/(?<=[\+\-=\/\*])[\w]+(\.[0-9]+)?/)[0];
            console.log("Before String: " + value);
            value = String(value);
            console.log(value);

            statChange(that.match(/f?[A-Z]+/),value,calc[0])
            displayError(that, "info")

            console.log("calc: " + calc)
            if (calc.length > 1) {
                matches[i] = that.replace(/[\+\-=\/\*][\w\.]+/, "");
                console.log(matches[i]);
                calc.splice(0,1);
                i--;
            }
        }
    }

    rpgFormat(text, container);

}

function rpgFormat(text, container) {
    let replacing = text.match(/\?[A-Z]+/g);
    let rules = text.match(/\(?f?[A-Z]+([<>!]=?|==)[0-9A-Za-z_]+(\.[0-9]+)?\)?/g);
    let changes = text.match(/f?[A-Z]+([\+\-=\/\*][0-9\w]+(\.[0-9]+)?)+/g);
    
    let hidden = text.match(/([\w\.=<>!]*)\[.*?\]/g);
    if (hidden) {
        for (i=0; i<hidden.length; i++) {
            // that = hidden[i].replace("<", "&lt;").replace(">", "&gt;");
            that = hidden[i];
            console.log("hiding: " + that);
            let hideRule = that.match(/^(.*)(?=\[)/)[0];
            let hideText = String(that.match(/\[.*\]/));
            // console.log(hideRule);

            if (hideRule) {
                console.log(hideRule);
                let operator = hideRule.match(/[<>=!]=?/);
                let split = hideRule.split(operator);
                console.log("split: " + split)

                if (statCheck(split[0], split[1], operator) || split.length < 2) {
                    //check failed
                    console.log("Failed check: " + hideRule, stats[split[0]]);
                    // hideRule = `<span class="rpg-fail">${hideRule}</span>`;
                    // hideText = `<span class='debug'>${hideText}</span>`
                    hideRule = "";
                    hideText = "";
                    // return button.addClass("disabled");
                } else {
                    //check succeeded
                    console.log("success! so show text: ", String(hideText).slice(1,-1));
                    hideRule = "";
                    hideText = `<span class='rpg-succeed'>${hideText.slice(1,-1)}</span>`;
                }

            } else {
                hideText = `<span class='debug'>${hideText}</span>`
            }

            console.log("Rule: ", hideRule, "Text: ", hideText)

            text = text.replace(that, hideRule + hideText);
        }
    }


    if (replacing) {
        for (i=0; i<replacing.length; i++) {
            that = replacing[i].replace("?", "");
            let replaceText = stats[that];
            // replaceText = replaceText.replace(/_/g, " ");
            if (replaceText == undefined) {
                replaceText = "--";
            }
            text = text.replace("?" + that, `<span class='rpg-replace'>${replaceText}</span>`);
            console.log("Replacing " + that + " with " + replaceText);
            // console.log(text);
            // container.html(text);
        }
    }
    if (rules) {
        for (i=0; i<rules.length; i++) {
            that = rules[i];
            // that = that.replace(">=", "&ge;").replace("<=", "&le;");
            // console.log(that);
            text = text.replace(that, `<span class='rpg-rule'>${that}</span>`);
        }
        // container.html(text.replace(">=", "&ge;").replace("<=", "&le;"));
    }
    if (changes) {
        for (i=0; i<changes.length; i++) {
            that = changes[i];
            text = text.replace(that, `<span class='rpg-change'>${that}</span>`);
            // container.html(text);
        }
    }

    
    text = text.replace("!=", "&ne;").replace("==", "=").replace(">=", "&ge;").replace("<=", "&le;");
    // text = "buffalo";
    container.html(text);
}

function statChange(str, num, calc) {
    console.log("stat change: " + str + calc + num);
    if (!stats[str] || stats[str] == undefined) {
        stats[str] = 0;
    }
    console.log("Current Value:" + stats[str]);
    if (stats[num]) {
        num = stats[num];
    }

    if (!(/\d/).test(num) && calc != "=") {
        console.log("error");
        displayError("Error: can only do math with numbers", "danger");
        return;
    } 

    switch (String(calc)) {
        case "=":
            stats[str] = num;
            break;
        case "+":
            stats[str] -= -parseFloat(num);
            break;
        case "-":
            stats[str] -= parseFloat(num);
            break;
        case "*":
            stats[str] = stats[str] * parseFloat(num);
            break;
        case "/":
            stats[str] = stats[str] / parseFloat(num);
            break;

    }
    console.log("New Value: " + stats[str]);
    renderStats(str);
}

function wordChange(type, word) {
    if (stats[word] == undefined) {
        stats[type] = word;
    } else {
        stats[type] = stats[word];
    }
    renderStats(type);
}

function checkAbility(text, button) {
    let matches = text.match(/[A-Zf]+[<>=!]=?([\-0-9\.]|[A-Za-z_])+/g);
    let returnValue = text.includes("RETURN");
    // console.log(matches);
    

    rpgFormat(text, button.find(".choice-text"));

    if (returnValue) {
        console.log("Return to checkpoint", stats["CHECKPOINT"]);
        button.data("link", stats["CHECKPOINT"]);
    }

    if (!matches || matches.length == 0 || debug == true) {
        return button.removeClass("disabled");
    }
    for (i=0; i<matches.length; i++) {
        that = matches[i];

        let operator = that.match(/[<>=!]=?/);
        let split = that.split(operator);

        if (statCheck(split[0], split[1], operator)) {
            console.log("Failed check: " + that, stats[split[0]]);
            return button.addClass("disabled");
        }
    }
    return button.removeClass("disabled");
}

function statCheck(str, num, calc) {
    // let testing = num;
    if (!stats[str] || stats[str] == undefined) {
        stats[str] = 0;
    }
    let statValue = stats[str];
    // console.log(calc);
    console.log("Stat value: " + statValue);
    calc = String(calc);
    switch(calc) {
        case "=":
            return false;
        case "<":
            console.log("check if less than")
            if (statValue < parseFloat(num)) {
                return false;
            }
            break;
        case ">":
            console.log("check if greater than")
            if (statValue > parseFloat(num)) {
                return false;
            }
            break;
        case "==":
            console.log("check if equal");
            if (statValue == num || statValue == parseFloat(num)) {
                return false;
            }
            break;
        case "!=":
            console.log("check if not equal");
            if (statValue != num && statValue != parseFloat(num)) {
                return false;
            }
            break;
        case ">=":
            console.log("check if greater than or equal");
            if (statValue >= parseFloat(num)) {
                return false;
            }
            break;
        case "<=":
            console.log("check if less than or equal");
            if (statValue <= parseFloat(num)) {
                return false;
            }
            break;
            
        default: 
            console.log("ERROR");
            return true;
    }
    return true;
}

function renderStats(stat) {
    let value = stats[stat];
    console.log("Rendering: " + String(stat), value);
    if (String(stat).startsWith("f")) {
        return; // do not display flag stats
    }
    if ($("#stat-" + stat).length == 0) {
        $(".rpg").append(`<div id='stat-${stat}' data-stat='${stat}'><h2>${stat}: <span id='${stat}-counter'>${value}</span></h2></div>`)
    } else {
        $(`#${stat}-counter`).html(value);
    }

    if (value == 0 || value == "" || value == undefined || !value) {
        $(`#stat-${stat}`).hide();
        console.log("hiding stat");
    } else {
        $(`#stat-${stat}`).show();
    }
}

$("body").on("click", ".restart", function() {
    stats = [];
    // variables = [];
    stats["SCORE"] = 0;
    console.log(stats);
    $(".rpg").html(`
        <div id='stat-SCORE' data-stat='SCORE'><h2>SCORE: <span id='SCORE-counter'></span></h2></div>`);
});

$("#toggleRPG").html(`
    <button class='btn btn-secondary' type='button' id='toggleRPGButton' data-bs-toggle='collapse' data-bs-target='#rpg'>Toggle RPG</button>
`);

//add button rule
$("#addRule").html(`
    <div class='input-group'>
        <input type='text' id='rule-name' class='form-control' placeholder='variable'>
        <select id='rule-operator' class='form-select'>
            <option value='=='>Equal</option>
            <option value='>'>Greater Than</option>
            <option value='<'>Less Than</option>
            <option value='!='>Not Equal</option>
            <option value='<='>Less Than or Equal To</option>
            <option value='>='>Greater Than or Equal To</option>
        </select>
        <input type='text' id='rule-value' class='form-control' placeholder='value'>
        <select id='rule-location' class='form-select'>
            <option value='option1'>Option 1</option>
            <option value='option2'>Option 2</option>
            <option value='option3'>Option 3</option>
        </select>
        <button class='btn btn-primary rpg-button' type='button' data-type='rule' id='addRuleButton'>Add Rule</button>
    </div>
`)
$("#addChange").html(`
    <input type='checkbox' id='rpgHidden'>
    <label for='rpgHidden'>Hidden change?</label>
    <input type='checkbox' id='rpgFlag'>
    <label for='rpgFlag'>Hidden Stat?</label>
    <div class='input-group'>
        <input type='text' id='change-name' class='form-control' placeholder='variable'>
        <select id='change-operator' class='form-select'>
            <option value='='>Set To</option>
            <option value='+'>Add</option>
            <option value='-'>Minus</option>
            <option value='?'>Display Value</option>
        </select>
        <input type='text' id='change-value' class='form-control' placeholder='value'>
        <select id='change-location' class='form-select'>
            <option value='area'>Area</option>
            <option value='option1'>Option 1</option>
            <option value='option2'>Option 2</option>
            <option value='option3'>Option 3</option>
        </select>
        <button class='btn btn-primary rpg-button' type='button' data-type='change' id='addChangeButton'>Change Stats</button>
    </div>
`)

$(".areaInput").on("click", function() {
    thisId = $(this).attr("id");
    $("#rule-location").val(thisId);
    $("#change-location").val(thisId);
})
$("body").on("click", ".rpg div", function() {
    let name = $(this).data("stat");
    $("#change-name").val(name);
    $("#rule-name").val(name);
})

$("body").on("click", ".rpg-button", function() {
    let type = $(this).data("type");

    let ruleName = $(`#${type}-name`).val().toUpperCase().replace(/[^A-Z0-9_]/g, '');
    let ruleOperator = $(`#${type}-operator`).val();
    let ruleValue = $(`#${type}-value`).val();
    let ruleLocation = $(`#${type}-location`).val();

    if (ruleName == "" || ruleValue == "" || ruleLocation == "") {
        if (ruleOperator != "?") {
            return alert("Please fill in all fields");
        }
    }
    if ($("#rpgFlag").is(":checked")) {
        ruleName = "f" + ruleName;
    }

    // Add rule to selected option
    let option = $("#" + ruleLocation);
    let currentText = option.val();
    if (currentText.includes(ruleName + ruleOperator + ruleValue)) {
        return alert("Rule already exists on this option");
    }

    let addRule = `${ruleName}${ruleOperator}${ruleValue}`;
    if ($("#rpgHidden").is(":checked")) {
        addRule = `[${addRule}]`;
    }

    if (ruleOperator == "?") {
        addRule = `?${ruleName}`;
    }

    option.val(`${currentText} ${addRule}`);

    // Clear inputs
    $(`#${type}-name`).val("");
    $(`#${type}-value`).val("");
    option.focus();
});