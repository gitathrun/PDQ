#
#  $Id: Makefile,v 4.3 2009/04/03 02:19:41 pfeller Exp $
#
#--------------------------------------------------------------------------

#CFLAGS  = -ggdb
CFLAGS  = -O
EXT     = .exe
CC      = gcc
LIB     = ../../../lib
INCL    = ../../../lib

PGM     = \
          abcache \
          multibus

#--------------------------------------------------------------------------

all:	${PGM}

#--------------------------------------------------------------------------

abcache: abcache.c
	${CC} ${CFLAGS} -o abcache abcache.c -I $(INCL) -L$(LIB) -lpdq -lm

multibus: multibus.c
	${CC} ${CFLAGS} -o multibus multibus.c -I $(INCL) -L$(LIB) -lpdq -lm

#--------------------------------------------------------------------------

ln:
	ln ../../../java/examples/Ch7_*.out .

clean:
	-/bin/rm ${PGM} *.o *.exe *~ *.log *.tst Ch7_*.out

#--------------------------------------------------------------------------

test:
	./test.sh

#--------------------------------------------------------------------------

