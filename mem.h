
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

//char *str_dup( const char *str );
void free_string ( char *str );
void free_string_list( STRING_LIST *pString );
