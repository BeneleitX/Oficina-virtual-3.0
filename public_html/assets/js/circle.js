function Circlebar(e) {
    this.element = $(e.element), this.element.append('<div class="spinner-holder-one animate-0-25-a"><div class="spinner-holder-two animate-0-25-b"><div class="loader-spinner" style=""></div></div></div><div class="spinner-holder-one animate-25-50-a"><div class="spinner-holder-two animate-25-50-b"><div class="loader-spinner"></div></div></div><div class="spinner-holder-one animate-50-75-a"><div class="spinner-holder-two animate-50-75-b"><div class="loader-spinner"></div></div></div><div class="spinner-holder-one animate-75-100-a"><div class="spinner-holder-two animate-75-100-b"><div class="loader-spinner"></div></div></div>'), this.value, this.maxValue, this.counter, this.dialWidth, this.size, this.fontSize, this.fontColor, this.skin, this.triggerPercentage, this.type, this.timer;

    var t = this.element[0].dataset,
        i = this;
    this.initialise = function() {
        i.value = parseInt(t.circleStarttime) || parseInt(e.startTime) || 0, i.maxValue = parseInt(t.circleMaxtime) || parseInt(e.maxValue) || 60, i.counter = parseInt(t.circleCounter) || parseInt(e.counter) || 1e3, i.dialWidth = parseInt(t.circleDialwidth) || parseInt(e.dialWidth) || 5, i.size = t.circleSize || e.size || "150px", i.fontSize = t.circleFontsize || e.fontSize || "20px", i.fontColor = t.circleFontcolor || e.fontColor || "rgb(135, 206, 235)", i.skin = t.circleSkin || e.skin || " ", i.triggerPercentage = t.circleTriggerpercentage || e.triggerPercentage || !1, i.type = t.circleType || e.type || "timer", i.element.addClass(i.skin).addClass("loader"), i.element.find(".loader-bg").css("border-width", i.dialWidth + "px"), i.element.find(".loader-spinner").css("border-width", i.dialWidth + "px"), i.element.css({
            width: i.size,
            height: i.size
        }), i.element.find(".loader-bg .text").css({
            "font-size": i.fontSize,
            color: i.fontColor
        })
    }, this.initialise(), this.renderProgress = function(e) {
        e = Math.floor(e);
        var t = 0;
        e < 25 ? (t = -90 + e / 100 * 360, i.element.find(".animate-0-25-b").css("transform", "rotate(" + t + "deg)"), i.triggerPercentage && i.element.addClass("circle-loaded-0")) : e >= 25 && e < 50 ? (t = -90 + (e - 25) / 100 * 360, i.element.find(".animate-0-25-b").css("transform", "rotate(0deg)"), i.element.find(".animate-25-50-b").css("transform", "rotate(" + t + "deg)"), i.triggerPercentage && i.element.removeClass("circle-loaded-0").addClass("circle-loaded-25")) : e >= 50 && e < 75 ? (t = -90 + (e - 50) / 100 * 360, i.element.find(".animate-25-50-b, .animate-0-25-b").css("transform", "rotate(0deg)"), i.element.find(".animate-50-75-b").css("transform", "rotate(" + t + "deg)"), i.triggerPercentage && i.element.removeClass("circle-loaded-25").addClass("circle-loaded-50")) : e >= 75 && e <= 100 && (t = -90 + (e - 75) / 100 * 360, i.element.find(".animate-50-75-b, .animate-25-50-b, .animate-0-25-b").css("transform", "rotate(0deg)"), i.element.find(".animate-75-100-b").css("transform", "rotate(" + t + "deg)"), i.triggerPercentage && i.element.removeClass("circle-loaded-50").addClass("circle-loaded-75"))
    }
       
    var t = 0,
        a = 0,
        r = i.element.find(".text");

    i.timer = setInterval(function() {
        if( i.value < i.maxValue ){
             i.value += parseInt(i.counter / 1e3);
             t = 100 * i.value / i.maxValue;
             i.renderProgress(t);
             a = new Date(null);
             a.setSeconds(i.value);
        }
        else{ 
            reload_captcha();
          //  clearInterval(i.timer);
        }

    }, i.counter);

        

    
}
