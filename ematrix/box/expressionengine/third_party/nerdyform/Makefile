.PHONY : dist clean

clean:
	-rm -- dist/nerdyform.zip

dist:
	git archive --format=zip -o dist/nerdyform.zip --prefix="nerdyform/" HEAD
