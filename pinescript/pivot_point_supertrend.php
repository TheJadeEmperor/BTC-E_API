
<?

// This source code is subject to the terms of the Mozilla Public License 2.0 at https://mozilla.org/MPL/2.0/
// © LonesomeTheBlue

//@version=4
strategy("Pivot Point Strategy PPS Alerts", overlay = true)

prd = input(defval = 2, title="Pivot Point Period", minval = 1, maxval = 50)
Factor=input(defval = 3, title = "ATR Factor", minval = 1, step = 0.1)
Pd=input(defval = 10, title = "ATR Period", minval=1)
showpivot = input(defval = false, title="Show Pivot Points")
showlabel = input(defval = true, title="Show Buy/Sell Labels")
showcl = input(defval = false, title="Show PP Center Line")
showsr = input(defval = false, title="Show Support/Resistance")

float ph = na
float pl = na
ph := pivothigh(prd, prd)
pl := pivotlow(prd, prd)


plotshape(ph and showpivot, text="H",  style=shape.labeldown, color=na, textcolor=color.purple, location=location.abovebar, transp=0, offset = -prd)
plotshape(pl and showpivot, text="L",  style=shape.labeldown, color=na, textcolor=color.lime, location=location.belowbar, transp=0, offset = -prd)

float center = na
center := center[1]
float lastpp = ph ? ph : pl ? pl : na
if lastpp
    if na(center)
        center := lastpp
    else
        center := (center * 2 + lastpp) / 3

Up = center - (Factor * atr(Pd))
Dn = center + (Factor * atr(Pd))

float TUp = na
float TDown = na
Trend = 0
TUp := close[1] > TUp[1] ? max(Up, TUp[1]) : Up
TDown := close[1] < TDown[1] ? min(Dn, TDown[1]) : Dn
Trend := close > TDown[1] ? 1: close < TUp[1]? -1: nz(Trend[1], 1)
Trailingsl = Trend == 1 ? TUp : TDown

linecolor = Trend == 1 and nz(Trend[1]) == 1 ? color.lime : Trend == -1 and nz(Trend[1]) == -1 ? color.purple : na
plot(Trailingsl, color = linecolor ,  linewidth = 5, title = "PP SuperTrend")

plot(showcl ? center : na, color = showcl ? center < hl2 ? color.blue : color.red : na, transp = 0)

bsignal = Trend == 1 and Trend[1] == -1
ssignal = Trend == -1 and Trend[1] == 1
plotshape(bsignal and showlabel ? Trailingsl : na, title="Buy", text="Buy", location = location.absolute, style = shape.labelup, size = size.tiny, color = color.lime, textcolor = color.black, transp = 0)
plotshape(ssignal and showlabel ? Trailingsl : na, title="Sell", text="Sell", location = location.absolute, style = shape.labeldown, size = size.tiny, color = color.purple, textcolor = color.white, transp = 0)

float resistance = na
float support = na
support := pl ? pl : support[1]
resistance := ph ? ph : resistance[1]

plot(showsr and support ? support : na, color = showsr and support ? color.lime : na, style = plot.style_circles, offset = -prd)
plot(showsr and resistance ? resistance : na, color = showsr and resistance ? color.red : na, style = plot.style_circles, offset = -prd)

//alertcondition(Trend == 1 and Trend[1] == -1, title='Buy Signal', message='Buy Signal')
//alertcondition(Trend == -1 and Trend[1] == 1, title='Sell Signal', message='Sell Signal')
//alertcondition(change(Trend), title='Trend Changed', message='Trend Changed')

//draw arrows on chart
if Trend == 1 and Trend[1] == -1 
    strategy.entry("PPL", strategy.long, comment="PPS Long")
    strategy.cancel("PPS")

if Trend == -1 and Trend[1] == 1 
    strategy.entry("PPS", strategy.short, comment="PPS Short")
    strategy.cancel("PPL")

?>