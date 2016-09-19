.PHONY: demo

demo:
	docker build -t tracy-profiler-demo .
	docker run --rm -p 8080:80 --name tracy-profiler-demo tracy-profiler-demo
