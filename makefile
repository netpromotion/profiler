.PHONY: demo demo-build demo-run

demo:
	composer install
	sudo make demo-build demo-run

demo-build:
	chmod 0777 demo/nette/log -R
	chmod 0777 demo/nette/temp -R
	docker build -t tracy-profiler-demo .

demo-run:
	docker run --rm -p 8080:80 --name tracy-profiler-demo tracy-profiler-demo
