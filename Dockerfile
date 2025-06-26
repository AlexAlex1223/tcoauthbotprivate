FROM php:8.1-cli
RUN mkdir /app
WORKDIR /app
COPY . /app
RUN apt-get update && apt-get install -y unzip curl
EXPOSE 10000
CMD ["php", "-S", "0.0.0.0:10000", "bot.php"]
