let stats = [];
stats["SCORE"] = 0;
stats["CHECKPOINT"] = 1;
// let variables = [];

$("body").append(`<div class='rpg'>
    <div id='stat-SCORE'><h2>SCORE: <span id='SCORE-counter'></span></h2></div></div>`);

function checkScore(text, container) {
    let matches = text.match(/[A-Z]+[\+\-=][0-9\.]+/g);
    let wordMatches = text.match(/[A-Z]+=[A-Za-z]+/g);
    let checkpoint = text.includes("CHECKPOINT");

    if (checkpoint) {
        let saveArea = $("#currentArea").val();
        stats["CHECKPOINT"] = saveArea;
        console.log("Checkpoint set to " + saveArea);
    }

    if (matches) {
        for (i=0; i<matches.length; i++) {
            that = matches[i];
            statChange(that.match(/[A-Z]+/),that.match(/[0-9\.]+/),that.match(/[\+\-=]/))
            displayError(that, "info")
        }
    }
    // Handle word matches
    if (wordMatches) {
        for (i=0; i<wordMatches.length; i++) {
            that = wordMatches[i].split("=");
            wordChange(that[0],that[1])
        }
    }

    rpgFormat(text, container);

}

function rpgFormat(text, container) {
    let replacing = text.match(/\?[A-Z]+/g);
    let rules = text.match(/\(?[A-Z]+([<>]=?|==)[0-9\.A-Za-z]+\)?/g);
    let changes = text.match(/\(?[A-Z]+[\+\-=][0-9\.A-Za-z]+\)?/g);
    
    // let inputField = text.match(/INPUT:[A-Z]+/);

    // if (inputField) {
    //     let inputName = inputField[0].split(":")[1];

    //     let html = `
    //     <div class="input-group">
    //         <span class="input-group-text">${inputName}:</span>
    //         <input type='text' id='${inputName}' class='rpg-input form-control' placeholder='Enter ${inputName}'>
    //         <div class="input-group-append">
    //             <button class="btn btn-secondary" type="button" data-choice="1" data-link="">Submit</button>
    //         </div>
    //     </div>`;
    //     container.html(html);
    // }
    if (replacing) {
        for (i=0; i<replacing.length; i++) {
            that = replacing[i].replace("?", "");
            let replaceText = stats[that];
            if (replaceText == undefined) {
                replaceText = "--";
            }
            text = text.replace("?" + that, `<span class='rpg-replace'>${replaceText}</span>`);
            console.log("Replacing " + that + " with " + replaceText);
            // console.log(text);
            container.html(text);
        }
    }
    if (rules) {
        for (i=0; i<rules.length; i++) {
            that = rules[i];
            text = text.replace(that, `<span class='rpg-rule'>${that}</span>`);
            container.html(text);
        }
    }
    if (changes) {
        for (i=0; i<changes.length; i++) {
            that = changes[i];
            text = text.replace(that, `<span class='rpg-change'>${that}</span>`);
            container.html(text);
        }
    }

    let hidden = text.match(/\[.*\]/);
    if (hidden) {
        for (i=0; i<hidden.length; i++) {
            that = hidden[i];
            text = text.replace(that, `<span class='debug'>${that}</span>`);
            container.html(text);
        }
    }
}

function statChange(str, num, calc) {
    if (!stats[str] || stats[str] == undefined) {
        stats[str] = 0;
    }

    switch (String(calc)) {
        case "=":
            stats[str] = num;
            break;
        case "+":
            stats[str] -= -num;
            break;
        case "-":
            stats[str] -= num;
            break;

    }
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
    let matches = text.match(/[A-Z]+[<>=]=?[0-9\.A-Za-z]+/g);
    let returnValue = text.includes("RETURN");
    console.log(matches);
    

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

        let operator = that.match(/[<>=]=?/);
        let split = that.split(operator);

        if (statCheck(split[0], split[1],operator)) {
            console.log("Failed check: " + that, stats[split[0]]);
            return button.addClass("disabled");
        }
    }
    // return button.removeClass("disabled");
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
            console.log(statValue == num || statValue == parseFloat(num))
            console.log("value: " + statValue, "number: " + num)
            if (statValue == num || statValue == parseFloat(num)) {
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
    if ($("#stat-" + stat).length == 0) {
        $(".rpg").append(`<div id='stat-${stat}'><h2>${stat}: <span id='${stat}-counter'>${stats[stat]}</span></h2></div>`)
    } else {
        $(`#${stat}-counter`).html(stats[stat]);
    }
}
function renderWord(variable) {
    if ($("#stat-" + variable).length == 0) {
        $(".rpg").append(`<div id='stat-${variable}'><h2>${variable}: <span id='${variable}-counter'>${stats[variable]}</span></h2></div>`)
    } else {
        $(`#${variable}-counter`).html(stats[variable]);
    }
}

$("body").on("click", ".restart", function() {
    stats = [];
    // variables = [];
    stats["SCORE"] = 0;
    console.log(stats);
    $(".rpg").html(`
        <div id='stat-SCORE'><h2>SCORE: <span id='SCORE-counter'></span></h2></div>`);
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

    // Add rule to selected option
    let option = $("#" + ruleLocation);
    let currentText = option.val();
    if (currentText.includes(ruleName + ruleOperator + ruleValue)) {
        return alert("Rule already exists on this option");
    }

    let addRule = `(${ruleName}${ruleOperator}${ruleValue})`;

    if (ruleOperator == "?") {
        addRule = `?${ruleName}`;
    }

    option.val(`${currentText} ${addRule}`);

    // Clear inputs
    $(`#${type}-name`).val("");
    $(`#${type}-value`).val("");
    option.focus();
});