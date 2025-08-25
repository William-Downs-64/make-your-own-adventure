let stats = [];
stats["SCORE"] = 0;

$("body").append(`<div class='rpg'>
    <div id='stat-SCORE'><h2>SCORE: <span id='SCORE-counter'></span></h2></div></div>`);

function checkScore(text) {
    let matches = text.match(/[A-Z]+[\+\-<>=][0-9\.]+/g)
    console.log(matches);

    if (!matches || matches.length == 0) {
        return;
    }
    for (i=0; i<matches.length; i++) {
        that = matches[i];
        statChange(that.match(/[A-Z]+/),that.match(/[0-9\.]+/),that.match(/[\+\-<>=]/))
    }
    // let scoreChange = 
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
        // case "<":
        //     if (stats[str] < num) {
        //         return true;
        //     } else {return false}
        // case ">":
        //     if (stats[str] > num) {
        //         return true;
        //     } else {return false}

    }
    renderStats(str);
}

function checkAbility(text) {
    let matches = text.match(/[A-Z]+[<>=][0-9\.]+/g)
    console.log(matches);

    if (!matches || matches.length == 0 || debug == true) {
        return true;
    }
    for (i=0; i<matches.length; i++) {
        that = matches[i];

        if (statCheck(that.match(/[A-Z]+/),that.match(/[0-9\.]+/),that.match(/[<>=]/))) {
            return false;
        }
    }
    return true;
}

function statCheck(str, num, calc) {
    if (!stats[str] || stats[str] == undefined) {
        stats[str] = 0;
    }
    console.log(calc);
    if (calc == "<" && stats[str] < parseFloat(num)) {
        return false;
    } else if (calc == ">" && stats[str] > parseFloat(num)) {
        return false;
    } else if (calc == "=" && stats[str] == num) {
        return false;
    } else {
        return true;
    }
}

function renderStats(stat) {
    if ($("#stat-" + stat).length == 0) {
        $(".rpg").append(`<div id='stat-${stat}'><h2>${stat}: <span id='${stat}-counter'>${stats[stat]}</span></h2></div>`)
    } else {
        $(`#${stat}-counter`).html(stats[stat]);
    }
}

$("body").on("click", ".restart", function() {
    stats = [];
    stats["SCORE"] = 0;
    console.log(stats);
    $(".rpg").html(`
        <div id='stat-SCORE'><h2>SCORE: <span id='SCORE-counter'></span></h2></div>`);
});