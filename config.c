
#include <sys/stat.h>
#include <math.h>
#include <stdbool.h>
#include <stdarg.h>

#include "string.h"
#include "files.h"
#include "mem.h"

STRING_LIST *disallowed_php=NULL;
STRING_LIST *disallowed_js=NULL;

void load_config() {
 disallowed_php=fread_file( "./language/disallow_php.txt" );
 printf( "%d disallowed PHP functions\n", strings_in_list(disallowed_php) ); 
 disallowed_js=fread_file( "./language/disallow_js.txt" );
 printf( "%d disallowed JS functions\n", strings_in_list(disallowed_js) ); 
}

void unload_config() {
 free_string_list(disallowed_php);
 free_string_list(disallowed_js);
}
