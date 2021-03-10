<?
// This source code is subject to the terms of the Mozilla Public License 2.0 at https://mozilla.org/MPL/2.0/
// © TheJadeEmperor

//@version=4
strategy("New Pine Strategy", overlay = true)


highVol = false
oversold = false
overbought = false
uptrend = 0
downtrend = 0
trend = 0
cross = 0
bull = 0
bear = 0

plot(ema(close, 15))

//if close < close[1]  and close[1] < close[2] 
//    downtrend := true



//test for volume spike 
if volume > volume[1] * 2.5
    highVol := true
    bull := bull + 1
    

// bollinger bands
f_bb(src, length, mult) =>
    float basis = sma(src, length)
    float dev = mult * stdev(src, length)
    [basis, basis + dev, basis - dev]

[bbMiddle, bbUpper, bbLower] = f_bb(close, 20, 2)

plot(bbMiddle)
plot(bbUpper)
plot(bbLower)




float osValue = 0.08
float obValue = 0.08

//oversold | more than % below BB
if low < bbLower - (bbLower * osValue) and highVol
    oversold := true 
    strategy.entry("OS", strategy.long, comment="Panic Buy")
    strategy.cancel("OB")
    

//overbought | more than % above BB     
if high > bbUpper + (bbUpper * obValue) and highVol
    overbought := true
    strategy.entry("OB", strategy.short, comment="Panic Sell")
    strategy.cancel("OS")

    

rsiHigh = false
rsiLow = false
rsi = rsi(close, 14)

rsiPlot = 0

if rsi > 70
    rsiHigh := true
    bear := bear + 1
    if rsi > 85
        bear := bear + 1

if rsi < 30
    rsiLow := true
    bull := bull + 1
    if rsi < 20
        bull := bull + 1


underHorizon = false
aboveHorizon = false
macdGoldenCross = false
macdDeathCross = false

[macdLine, signalLine, histLine] = macd(close, 12, 26, 9)

//plot(macdLine, color=color.blue)
//plot(signalLine, color=color.orange)
//plot(histLine, color=color.red, style=plot.style_histogram)

//if crossunder(macdLine, signalLine)
//    macdDeathCross := true

if signalLine < 0
    underHorizon := true
    bull := bull + 1
    
if signalLine > 0 
    aboveHorizon := true
    bear := bear + 1
    
if signalLine > macdLine 
    bull := bull + 1
    
if signalLine < macdLine 
    bear := bear + 1

//close below Upper Band
//if high[1] > bbUpper and high[2] > bbUpper and close < bbUpper
if crossunder(close, bbUpper)
    cross := -1
    bear := bear + 1
      
    
// strategy.cancel()
        
//close above Lower Band
//if low[1] < bbLower and low[2] < bbLower and close > bbLower
if crossover(close, bbLower)
    cross := 1
    bull := bull + 1


//strategy.order("sell", false, 3, when = condition)   





// strategy.entry("PS", strategy.short, comment="Sell")
//    strategy.cancel("PB")
//  strategy.entry("PB", strategy.long, comment="Buy")
//strategy.cancel("PS")
        
        
//// macd golden & death cross \\\\
//plotshape(macdGoldenCross, style=shape.xcross, location=location.bottom, color=color.fuchsia, size=size.small)
//plotshape(macdDeathCross, style=shape.xcross, location=location.bottom, color=color.fuchsia, size=size.small)

//plotarrow(cross, colorup=color.purple, colordown=color.white, maxheight=30, transp=0) 
//plotarrow(trend, colorup=color.blue, colordown=color.white, maxheight=30, transp=0) 

//// rsi high and low \\\\
//plotshape(rsiHigh, style=shape.xcross, location=location.top, color=color.maroon, size=size.large)
//plotshape(rsiLow, style=shape.xcross, location=location.top, color=color.maroon, size=size.normal)

//// panic buy & sell \\\\
plotshape(oversold, style=shape.xcross, location=location.bottom, color=color.green, size=size.large)
plotshape(overbought, style=shape.xcross, location=location.top, color=color.red, size=size.large)

//// volume spike \\\\
//plotshape(highVol, style=shape.xcross, location=location.bottom, color=color.lime, size=size.small)

?>