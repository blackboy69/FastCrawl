function hash(strInput)
{
    var nResult = 7;
    var strKey = "acdfgilmnoprstuw";
    for(var i = 0; i < strInput.length; i++)
    {
        var nIndex = strKey.indexOf(strInput[i]);
        if(nIndex == -1)
        {
            nResult = -1;
            break;
        }
        nResult = (nResult * 23 + nIndex);
    }
     return nResult;
}

function guessHash(len, hashSearch)
{
    var strKey = "acdfgilmnoprstuw";    
    var currStr = new Array(len + 1).join( "a" );
    
    var combos = Permutations(strKey.split(""));
    
    
    // this is going to be really really ugly.
    for (var k = 0 ; k< len ; k++)
    {
        currStr[k] 
        for (var i = len ; i>=0 ; i--)
        {
            for(var j = 0; j<strKey.Length; j++)
            {
                // iterate every possible match
                currStr[i] = strKey[j];
                if (hashSearch == hash(currStr))
                {
                    // we got it!
                    return currStr;
                }            
            }
        }
    }

    return "Not Found";
}