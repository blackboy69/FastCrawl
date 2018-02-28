<?
# this class makes it easy to generate html from a sql statement.

include_once ("scrape/db.inc");

class db_html
{
   public function generateSelect($sql,$idField,$valueField,$selected=false)
   {
      $result = mysql_query($sql);
      $out = "";
      if ($result)
      {
         while($row = mysql_fetch_assoc($result))
         {
            $selectedHtml = "";
            if ( $row[$idField] == $selected ) 
            {
               $selectedHtml = "SELECTED";
            }
            $out .= "<option $selectedHtml value='{$row[$idField]}'>{$row[$valueField]}\n";
         }
      }
      return $out;
   }
   /* 
      OPTIONS ALLOW CERTAIN fields to be linked. 
      so if options[link][title]= 'url';
         the title will have the href with of the field url

         options['skip']['url'] =true
         will cause the url field to be skipped an not displayed in the output

         options['format_date']['post_date'] = 'F j, Y, g:i a';
         will format the according to date() format;
   */

   public function generateTable($sql,$options=array())
   {
      $result = mysql_query($sql);
      $out = "";
      if ($result)
      {
         $out .= "<table>\n";
         $i=0;
         while($row = mysql_fetch_assoc($result))
         {

            if ($i++==0)
            {

               $out .="<tr>\n";
               foreach($row as $key => $val)
               {
                  if (! isset($options['skip'][$key]))
                  {
                     $key = ucwords(preg_replace("/_/"," ",$key));

                     $out .= "<th>$key</th>\n";
                  }

               }
               $out .= "</tr>";
            }
            $out .="<tr>\n";

            foreach($row as $key => $val)
            {
               if (! isset($options['skip'][$key]))
               {
                  if (isset($options['link'][$key]))
                  {
                     $val = "<a href='{$row[$options['link'][$key]]}'>$val</a>";
                  }
                  if (isset($options['format_date'][$key]))
                  {
                     $val = date($options['format_date'][$key],strtotime($val));
                  }
                  $out .= "<td>$val</td>\n";
               }
            }
            $out .= "</tr>";
         }
         if ($i==0)
         {
            $out .="<tr><td>No Results Found</td></tr>";
         }
         $out .= "</table>\n";
      }
      else
      {
         return "$sql <p>". mysql_error();
      }
      return $out;
   }

}