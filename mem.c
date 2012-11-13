// Empire in the Sky
// Copyright (c) 2008 H. Elwood Gilliland III

#if defined(BSD)
#include <types.h>
#else
#include <sys/types.h>
#endif
#include <ctype.h>
#include <stdio.h>
#include <stdlib.h>
#include <string.h>
#include <time.h>
#include <math.h>
#include <stdbool.h>

#include <malloc.h>

#include "string.h"

/*
 * Duplicate a string into dynamic memory.
 */
//char *str_dup( const char *str )
//{
// return strdup(str);
//}

void free_string ( char *str ) {
 free(str);
}

void free_string_list( STRING_LIST *pString ) {
 STRING_LIST *p,*n;
 for ( p=pString; p; p=n ) {
  n=p->next;
  if ( p->str ) free(p->str);
  free(p);
 }
}
