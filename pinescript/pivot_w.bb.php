<?
// This source code is subject to the terms of the Mozilla Public License 2.0 at https://mozilla.org/MPL/2.0/
// © LonesomeTheBlue

//@version=4
strategy("Pivot Point Strategy PPS Alerts", overlay = true)


// BB inputs
bb_length       = input(50, minval=1, title="BB Period") //length for bb calculation
bb_mult         = input(4.0, minval=0.001, maxval=50, title="BB StdDev") //stdevfor bb
bbCutoff          = input(0.4, minval=0.01, maxval=1, step=0.1, title="BB % Cutoff for Nigging") //if bb width is < cutoff, it is sideways trend
bbCutoffHigh    = input(1.0, minval=0.01, maxval=10, step=0.1, title="BB % Cutoff for Panic Selling") //if bb width > bbCutoffHigh, enter danger zone

bbCutoffLow = bbCutoff

stopNigging     = input(defval = false, title="Stop Nigging During Sideways Trend")

//pivot point periods inputs
pppHigh         = input(defval = 2, title="PPP High Period", minval = 1, maxval = 50) 

pppLow          = input(defval = 2, title="PPP Low Period", minval = 1, maxval = 50)

atrFactor       = input(defval = 3, title = "ATR Factor", minval = 1, step = 0.1)

atrPeriodHigh   = input(defval = 10, title = "ATR Period High", minval=1)
atrPeriodLow    = input(defval = 10, title = "ATR Period Low", minval=1)

showpivot       = input(defval = false, title="Show Pivot Points") 
showcl          = input(defval = false, title="Show PP Center Line")
showsr          = input(defval = false, title="Show Support/Resistance")


//create bollinger bands
f_bb(src, length, mult) =>
    float basis = sma(src, length)
    float dev = mult * stdev(src, length)
    [basis, basis + dev, basis - dev]

[bbMiddle, bbUpper, bbLower] = f_bb(close, bb_length, bb_mult)

bbWidthPercent = ( (bbUpper - bbLower) / bbMiddle )  //widht of bet. bbUpper & bbLower 

//determine sideways market 
is_sideways = bbWidthPercent < bbCutoff


bgcolor(is_sideways ? color.gray : color.black)  // Green is sideways trending market region and black is all others.

plot(bbMiddle, title="bbMiddle", linewidth=3)
plot(bbUpper, title="bbUpper")
plot(bbLower, title="bbLower")


float ph = na
float pl = na
float pivotHigh = na
float pivotLow = na
float resistance = na
float support = na

ph := pivothigh(pppHigh, pppHigh)
pl := pivotlow(pppLow, pppLow)

pivotHigh := ph
pivotLow := pl


float center = na //PP center line
center := center[1]
float lastpp = pivotHigh ? pivotHigh : pivotLow ? pivotLow : na //lastPP is either the curren pivot high or low

if lastpp //last pivot point
    if na(center) 
        center := lastpp
    else
        center := (center * 2 + lastpp) / 3


Up = center - (atrFactor * atr(atrPeriodHigh))
Dn = center + (atrFactor * atr(atrPeriodLow))

float TUp = na
float TDown = na
Trend = 0
TUp := close[1] > TUp[1] ? max(Up, TUp[1]) : Up
TDown := close[1] < TDown[1] ? min(Dn, TDown[1]) : Dn
Trend := close > TDown[1] ? 1: close < TUp[1]? -1: nz(Trend[1], 1)

Trailingsl = Trend == 1 ? TUp : TDown


//dealing with sideways markets
if is_sideways and stopNigging //stop nigging during sideways markets
    Trend := Trend[1] //keep the same trend throughout sideways range


bsignal = Trend == 1 and Trend[1] == -1
ssignal = Trend == -1 and Trend[1] == 1

linecolor = Trend == 1 and nz(Trend[1]) == 1 ? color.lime : Trend == -1 and nz(Trend[1]) == -1 ? color.purple : na


//// nigging \\\\\
tooWide = false //when BB width is too wide, panic sell 
if bbWidthPercent > bbCutoffHigh //BB width is chosen
    if bbWidthPercent < bbWidthPercent[1] //if width starts to decrease
        tooWide := true //plot this
        Trend := -1   //start short trend
        if Trend[1] == 1  //stop sell signal from appearing multiple times
            ssignal := true //fix sell signal display bug
            bsignal := false 

support := pivotLow ? pivotLow : support[1]
resistance := pivotHigh ? pivotHigh : resistance[1]

// plotshape(tooWide, text="W", color=color.silver, location = location.absolute)


//pivotHigh and pivotLow
plotshape(pivotHigh and showpivot, text="H",  style=shape.labeldown, color=na, textcolor=color.purple, location=location.abovebar, offset = -pppHigh)
plotshape(pivotLow and showpivot, text="L",  style=shape.labeldown, color=na, textcolor=color.lime, location=location.belowbar, offset = -pppLow)

//buy and sell signals
plotshape(bsignal ? Trailingsl : na, title="Buy", text="Buy", location = location.absolute, style = shape.labelup, size = size.tiny, color = color.lime, textcolor = color.black)
plotshape(ssignal ? Trailingsl : na, title="Sell", text="Sell", location = location.absolute, style = shape.labeldown, size = size.tiny, color = color.purple, textcolor = color.white)

//plot B & S trailing lines
plot(Trailingsl, color = linecolor ,  linewidth = 5, title = "PP SuperTrend")

//show center line 
plot(showcl ? center : na, color = color.blue, transp = 0)


plot(showsr and support ? support : na, color = showsr and support ? color.lime : na, style = plot.style_circles, offset = -pppHigh)
plot(showsr and resistance ? resistance : na, color = showsr and resistance ? color.red : na, style = plot.style_circles, offset = -pppLow)


//draw arrows on chart
if Trend == 1 and Trend[1] == -1 
    strategy.entry("PPL", strategy.long, comment="PPS Long")
    strategy.cancel("PPS")

if Trend == -1 and Trend[1] == 1 
    strategy.entry("PPS", strategy.short, comment="PPS Short")
    strategy.cancel("PPL")
    
?>