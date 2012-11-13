#include <stdio.h>
#include <stdlib.h>
#include <stdbool.h>
#include <string.h>

#include "string.h"
#include "files.h"
#include "mem.h"

char *supports[57] = {
"ilife","iphone","ipad","ipod",
"blackberry","blackberrykit","blackberrytouch",
"symbian","palm","palmweb",
"winmobile","winphone7",
"operamobile","garmin","brew","hiptop",
"android","androidkit",
"ie","msie","explorer","iexplore",
"firefox","ff","gecko","mozilla","moz",
"safari",
"seamonkeys","seamonkey",
"maxthon","flock","chromium","avant","orca","opera","s60","chrome","deepnet",
"xbox","playstation","nintendo",
"kindle",
"maemo","mylo",
"appletier",
"richcss",
"mobile","desktop","console",
"wap",
"tablet","smartphone",
"mobi","wml",
"konqueror","omniweb"
};

void interpret( char *index_Gc, char *device_File ) {
 STRING_LIST *index = fread_file( index_Gc );
 STRING_LIST *device = fread_file( device_File );
 STRING_LIST *stack=NULL;
 STRING_LIST *sL;
 char *user_agent, *http_accept;
 int i=0;
 fprintf( stdout, "Read files: %s,%s\n", index_Gc, device_File );
 for ( sL=device; sL; sL=sL->next ) {
  if ( i++ == 1 ) {
   user_agent=strlwr(sL->str);
  } else {
   http_accept=strlwr(sL->str);
  }
  if ( i>2 ) break;
 }
 fprintf( stdout, "user_agent: %s\nhttp_accept: %s\n", user_agent, http_accept );
 for ( sL=index; sL; sL=sL->next ) {
  char one[128]; 
  char two[128];
  char three[128];
  int on,tw,th;
  on=tw=th=0;
  char *p=sL->str;
  int len=strlen(sL->str);
  if ( prefix(sL->str,"#") ) { fprintf( stdout, "comment ignored\n" ); continue; }
  fprintf( stdout, "Line> %s (%d)\n", sL->str, strlen(sL->str) );
  one[0]='\0'; two[0]='\0'; three[0]='\0';
  while ( *p==' ' ) p++;
  while ( *p!='<' && *p!='=' && *p!=' ' && *p!='\0' ) { one[on++]=LOWER(*p); p++; }
  one[on]='\0';
  while ( *p==' ' ) p++;
  while ( (*p=='<' || *p=='=') && *p!='\0' ) { two[tw++]=LOWER(*p); p++; }
  two[tw]='\0';
  while ( *p==' ' ) p++;
  while ( *p != '\0' ) { 
   if ( *p == '0' || *p == '1' || *p == '2' || *p == '3' ||
        *p == '4' || *p == '5' || *p == '6' || *p == '7' ||
        *p == '8' || *p == '9' || *p == '.' ) three[th++]=LOWER(*p);
   p++;
  }
  three[th]='\0';
  fprintf( stdout, "......>%s %s %s", one, two, three );
  if ( !str_cmp(one, "go") ) {
   char *a=sL->str;
   a=first_Arg(a,two);
   a=first_Arg(a,two);
   if ( stack == NULL ) { 
    fprintf( stdout, "..%s", two );
    exit(1);
   } else {
    STRING_LIST *s;
    int found=0;
    for ( s=stack; s; s=s->next ) {
     switch ( s->type ) {
 case 0: if ( strstr(user_agent,"iphone") || strstr(user_agent,"ipod") ) found=1;  break;
 case 1: if ( strstr(user_agent,"iphone") ) found=1; break;
 case 2: if ( strstr(user_agent,"ipad") ) found=1; break;
 case 3: if ( strstr(user_agent,"ipod") ) found=1; break;
 case 4: if ( strstr(user_agent,"blackberry") || strstr(http_accept,"vnd.rim") ) found=1; break;
 case 5: if ( strstr(user_agent,"blackberry") && strstr(user_agent,"webkit") ) found=1; break;
 case 6: if ( strstr(user_agent,"blackberry95") || strstr(user_agent, "blackberry 98") ) found=1; break;
 case 36:
 case 7: if ( strstr(user_agent,"symbian") || strstr(user_agent,"series60") || strstr(user_agent,"series70") || strstr(user_agent,"series80") || strstr(user_agent,"series90") ) found=1; break;
 case 8: if ( !strstr(user_agent,"webos") && (strstr(user_agent,"palm") || strstr(user_agent,"blazer") || strstr(user_agent,"xiino")) ) found=1; break;
 case 9: if ( strstr(user_agent,"webos") ) found=1; break;
 case 10: if ( !strstr(user_agent,"windows phone os 7") && (strstr(user_agent,"windows ce") || strstr(user_agent, "windows") || strstr(user_agent,"iemobile") || (strstr(user_agent,"ppc")&&!strstr(user_agent,"macintosh")) )) found=1; break;
 case 11: if ( strstr(user_agent,"windows phone os 7") ) found=1; break;
 case 12: if ( strstr(user_agent,"opera") && strstr(user_agent,"mini") && strstr(user_agent,"mobi") ) found=1; break;
 case 13: if ( strstr(user_agent,"nuvifone") ) found=1; break;
 case 15: if ( strstr(user_agent,"brew") ) found=1; break;
 case 16: if ( strstr(user_agent,"hiptop") ) found=1; break;
 case 17: if ( strstr(user_agent,"android") || strstr(user_agent, "htc") ) found=1; break;
 case 18: if ( (strstr(user_agent,"htc") || strstr(user_agent,"android")) && strstr(user_agent,"webkit") ) found=1; break;
 case 20:
 case 21: if ( strstr(user_agent,"msie") ) found=1; break;
 case 22:
 case 23:
 case 24:
 case 25:
 case 26: if ( strstr(user_agent,"firefox") || strstr(user_agent,"gecko") || strstr(user_agent,"mozilla") ) found=1; break;
 case 27: if ( strstr(user_agent,"safari") ) found=1; break;
 case 28:
 case 29: if ( strstr(user_agent,"seamonkey") ) found=1; break;
 case 30: if ( strstr(user_agent,"maxthon") ) found=1; break;
 case 31:
 case 32: if ( strstr(user_agent,"chromium") || strstr(user_agent,"flock") ) found=1; break;
 case 33: if ( strstr(user_agent,"avant") ) found=1; break;
 case 34: if ( strstr(user_agent,"orca") ) found=1; break;
 case 35: if ( strstr(user_agent,"opera") ) found=1; break;
 case 37: if ( strstr(user_agent, "chrome") ) found=1; break;
 case 38: if ( strstr(user_agent, "deepnet") ) found=1; break;
 case 39: if ( strstr(user_agent, "xbox" ) ) found=1; break;
 case 40: if ( strstr(user_agent, "playstation" ) ) found=1; break;
 case 41: if ( strstr(user_agent, "nintendo") || strstr(user_agent,"wii") ) found=1; break;
 case 42: if ( strstr(user_agent, "kindle") ) found=1; break;
 case 43: if ( strstr(user_agent, "maemo") || strstr(user_agent,"tablet") ) found=1; break;
 case 44: if ( strstr(user_agent, "mylo") ) found=1; break;
 case 47:
 case 46:
 case 14:
 case 52:
 case 53:
 case 45: if ( strstr(user_agent, "iphone") || strstr(user_agent,"ipod") || strstr(user_agent,"maemo") || strstr(user_agent,"webos") || strstr(user_agent,"nuvifone") || strstr(user_agent,"tablet") || strstr(user_agent,"windows phone os 7") || strstr(user_agent,"maemo") || strstr(user_agent,"android") || strstr(user_agent,"webkit") ) found=1; break;
 case 48: if ( strstr(user_agent, "msie") || strstr(user_agent,"opera") || strstr(user_agent,"maxthon") || strstr(user_agent,"flock") || strstr(user_agent,"chrome") || strstr(user_agent,"firefox") || strstr(user_agent,"mozilla") ) found=1; break;
 case 49: if ( strstr(user_agent, "xbox") || strstr(user_agent,"playstation") || strstr(user_agent,"nintendo") || strstr(user_agent,"wii") ) found=1; break;
 case 50: if ( strstr(user_agent, "vnd.wap") || strstr(user_agent, "wml") ) found=1; break;
 case 51: if ( strstr(user_agent, "ipad") || strstr(user_agent,"android") || strstr(user_agent,"maemo")|| strstr(user_agent,"tablet") || strstr(user_agent,"mylo") ) found=1; break;
 case 54: if ( strstr(user_agent, "vnd.wap") || strstr(user_agent, "wml") ) found=1; break;
 case 55: if ( strstr(user_agent, "konqueror") ) found=1; break;
     }
     if ( found==1 ) fprintf( stdout, "...%s", two );
    }
   }
  } else {
   int x;
   for ( x=0; x<57; x++ ) if ( !str_cmp(one, supports[x]) ) {
    STRING_LIST *new=new_string_list();
    new->type=x;
    new->next=stack;
    stack=new;
   }
  }
 }
}
