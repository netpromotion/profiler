.PHONY: demo demo-build demo-run tests

demo:
	composer install
	sudo make demo-clean demo-build demo-run

demo-build:
	chmod 0777 demo/nette/log -R
	chmod 0777 demo/nette/temp -R
	docker build -t profiler-demo .

demo-run:
	docker run --rm -p 8080:80 --name profiler-demo profiler-demo

demo-clean:
	docker stop profiler-demo || true
	docker rm profiler-demo || true

tests:
	sudo docker run -v $$(pwd):/app --rm php:5.4-cli bash -c 'cd /app && php ./vendor/bin/phpunit'
