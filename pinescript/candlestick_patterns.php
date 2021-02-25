<?
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



//3 line strike

if close[1] < close[2] and open[1] < open[2] //3 red candles in a row
    if close[2] < close[3] and open[2] < open[3]
        //long green strike
        if close > open[3]
            LineStrike3 := true


//evening star pattern
heightGreen = close[2] - open[2] //long green candle
heightStar = open[1] - close[1] //evening star
heightRed = open - close //red candle

if heightGreen > 0 and heightStar > 0 and heightRed > 0 //valid sequence green-red-red
    if heightGreen > heightRed and heightRed > heightStar //
        //if open < close[1] and close[1] > close[2]
        eveningStar := false

//// 3 black crows
//find valid green candles
red0 = close - open
red1 = close[1] - open[1]
red2 = close[2] - open[2]
green3 = close[3] - open[3]
green4 = close[4] - open[4]

if close < close[1] and open < open[1] 
    if close[1] < close[2] and open[1] < open[2] //3 red in a row
        if green3 > 0 and green4 > 0 //valid green candles
            if close[3] > close[4] //green3 closes higher than green4
                BlackCrows3 := true
 


//// panic sell - 2 green 3 red
if red0 < 0 and red1 < 0 and red2 < 0
    if green3 > 0 and green4 > 0
        panicSell := true
        
        

//// 3 white soldiers 
green0 = close - open
green1 = close[1] - open[1]
green2 =  close[2] - open[2]
red3 =  close[3] - open[3]
red4 = close[4] - open[4]

//test 3 green candles
if green0 > 0 and green1 > 0 and green2 > 0
    if green0 > green1 and green1 > green2    //3 ascending candles
        if red3 < 0 and red4< 0    // 2 red candles
            whiteSoldiers3 := true
        
        
rsi = rsi(close, 14)

plot(ema(close, 15))
ema = ema(close, 15)
sma = sma(close, 15)

  
if high < high[1] and high[1] < high[2] and high[2] > high[3] and rsi > 80
    if ema > sma    
        isTop := true


//plotshape(panicSell, style=shape.xcross, location=location.abovebar, color=color.silver, size=size.small)


plotshape(isTop, style=shape.xcross, location=location.abovebar, color=color.white, size=size.normal)


plotshape(LineStrike3, style=shape.xcross, location=location.belowbar, color=color.green, size=size.small)

plotshape(BlackCrows3, style=shape.xcross, location=location.abovebar, color=color.orange, size=size.small)

plotshape(whiteSoldiers3, style=shape.xcross, location=location.belowbar, color=color.aqua, size=size.normal)





//if LineStrike3 or whiteSoldiers3
  //  strategy.entry("PB", strategy.long, comment="Buy")
//    strategy.order("buy", true, 1, when = uptrend and emaup)

//if BlackCrows3
    //strategy.entry("PS", strategy.short, comment="Sell")
//    strategy.order("sell", false, 1, when = downtrend and emadown)

?>