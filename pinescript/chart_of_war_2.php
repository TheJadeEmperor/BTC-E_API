<?php
// This source code is subject to the terms of the Mozilla Public License 2.0 at https://mozilla.org/MPL/2.0/
// © TheJadeEmperor

//@version=4
strategy("Candlestick patterns", overlay = true)

LineStrike3 = false
BlackCrows3 = false
eveningStar = false
validGreen = false
panicSell = false
whiteSoldiers3 = false
isTop = false


        
rsi = rsi(close, 14)
ema = ema(close, 15)
sma = sma(close, 15)

// plot(ema, color=color.blue)
// plot(sma, color=color.fuchsia)



//3 line strike

if close[1] < close[2] and open[1] < open[2] //3 red candles in a row
    if close[2] < close[3] and open[2] < open[3]
        //long green strike
        if close > open[3] 
            if close[1] < ema and close[2] < ema and close[3] < ema
                LineStrike3 := true



//// 3 black crows
//find valid green candles
red0 = close - open
red1 = close[1] - open[1]
red2 = close[2] - open[2]
green3 = close[3] - open[3]
green4 = close[4] - open[4]


if close < close[1] and open < open[1] //3 red in a row
    if close[1] < close[2] and open[1] < open[2] //3 red in a row
        if green3 > 0 and green4 > 0 //valid green candles
            if close[3] > close[4] //green3 closes higher than green4
                if close[1] < green4    //2nd red < 1st green 
                    BlackCrows3 := true
 



//// 3 white soldiers 
green0 = close - open
green1 = close[1] - open[1]
green2 =  close[2] - open[2]
red3 =  close[3] - open[3]
red4 = close[4] - open[4]

//test 3 green candles
if green0 > 0 and green1 > 0 and green2 > 0
    if green0 > green1 and green1 > green2    //3 ascending candles
        if red3 < 0 and red4 < 0    // 2 red candles
            if close[3] < ema and close[4] < ema //2 red under ema
                whiteSoldiers3 := true
        
    
height0 = close - open
height1 = close[1] - open[1]
//bear reversal
if high < high[1] and high[1] < high[2] and high[2] > high[3] and rsi > 65  
    //if height0 < 0 or height1 < 0 //at least 1 red candle    
    isTop := true



atrStopMultiplier = input(title="ATR for SL", type=input.float, defval=2)

//use ATR for stop loss
atr = atr(7)
longStop = close - atr * atrStopMultiplier
shortStop = close + atr * atrStopMultiplier
    
    
plot(longStop, style=plot.style_linebr, title="Trailing Stop", transp=0)
plot(shortStop, style=plot.style_linebr, title="Trailing Stop", transp=0)


// plotshape(isTop, style=shape.xcross, location=location.abovebar, color=color.white, size=size.normal)


// plotshape(LineStrike3, style=shape.xcross, location=location.belowbar, color=color.green, size=size.small)

// plotshape(BlackCrows3, style=shape.xcross, location=location.abovebar, color=color.orange, size=size.small)

// plotshape(whiteSoldiers3, style=shape.xcross, location=location.belowbar, color=color.aqua, size=size.normal)


 
//if LineStrike3 or whiteSoldiers3
    // strategy.entry("PB", strategy.long, comment="Buy")

// if BlackCrows3 or isTop
//    strategy.entry("PS", strategy.short, comment="Sell")

strategy.order("3LS", true, 1, when = LineStrike3)     
strategy.order("3WS", true, 1, when = whiteSoldiers3)     

strategy.order("BC3", false, 1, when = BlackCrows3)  
strategy.order("isTop", false, 1, when = isTop)  

?>