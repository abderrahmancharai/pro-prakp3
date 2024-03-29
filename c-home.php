<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <canvas id="chaosPendulum"></canvas>
    <title>Document</title>
</head>
<body>
    <style>
        html {
	height: 100%;
}
body {
	background-color: #424242;
	width: 100%;
	height: 100%;
	margin: 0px;
	font-family: Arial;
	overflow: hidden;
}


</style>

<script>
    class Pendulum {
	constructor(l1, l2, m1, m2, g) {
		this.g = g;

		this.l1 = l1;
		this.l2 = l2;

		this.m1 = m1;
		this.m2 = m2;

		this.a1 = Math.PI;
		this.a2 = Math.PI / 8;

		this.a1_v = 0;
		this.a2_v = 0;
	}

	tick() {
		this.a1_v += this.calcA1();
		this.a2_v += this.calcA2();

		this.a1 += this.a1_v;
		this.a2 += this.a2_v;
	}

	calcA1() {
		let num1 = -this.g * (2 * this.m1 + this.m2) * Math.sin(this.a1);
		let num2 = -this.m2 * this.g * Math.sin(this.a1 - 2 * this.a2);
		let num3 = -2 * Math.sin(this.a1 - this.a2) * this.m2;
		let num4 =
			this.a2_v * this.a2_v * this.l2 +
			this.a1_v * this.a1_v * this.l1 * Math.cos(this.a1 - this.a2);
		let den =
			this.l1 *
			(2 * this.m1 + this.m2 - this.m2 * Math.cos(2 * this.a1 - 2 * this.a2));
		return (num1 + num2 + num3 * num4) / den;
	}

	calcA2() {
		let num1 = 2 * Math.sin(this.a1 - this.a2);
		let num2 = this.a1_v * this.a1_v * this.l1 * (this.m1 + this.m2);
		let num3 = this.g * (this.m1 + this.m2) * Math.cos(this.a1);
		let num4 =
			this.a2_v * this.a2_v * this.l2 * this.m2 * Math.cos(this.a1 - this.a2);
		let den =
			this.l2 *
			(2 * this.m1 + this.m2 - this.m2 * Math.cos(2 * this.a1 - 2 * this.a2));
		return (num1 * (num2 + num3 + num4)) / den;
	}
}

class Visualization {
	constructor() {
		this.canvas = document.getElementById("chaosPendulum");
		this.ctx = this.canvas.getContext("2d");

		this.trail = [];
		this.batchSize = 10;
		this.frameRate = 120;
		this.trailLength = 200;

		this.resize();
		window.addEventListener("resize", this.resize.bind(this));

		this.lastTime = 0;
		requestAnimationFrame(this.draw.bind(this));
	}

	resize() {
		this.canvas.setAttribute("width", window.innerWidth);
		this.canvas.setAttribute("height", window.innerHeight);

		this.hangPos = {
			x: window.innerWidth / 2,
			y: window.innerHeight / 3
		};

		this.trail = [];

		let space = (window.innerHeight / 3) * 1.5;
		if (space > 500) space = 500;

		this.pendulum = new Pendulum((space / 3) * 2, space / 3, 50, 20, 0.1);
	}

	draw(time) {
		let x1, x2, y1, y2;

		while (this.lastTime < time) {
			this.pendulum.tick();
			this.lastTime += 1000 / this.frameRate;

			x1 = this.hangPos.x + this.pendulum.l1 * Math.sin(this.pendulum.a1);
			x2 = x1 + this.pendulum.l2 * Math.sin(this.pendulum.a2);

			y1 = this.hangPos.y + this.pendulum.l1 * Math.cos(this.pendulum.a1);
			y2 = y1 + this.pendulum.l2 * Math.cos(this.pendulum.a2);

			this.trail.push({
				x: x2,
				y: y2
			});
			if (this.trail.length > this.trailLength) this.trail.shift();
		}

		this.lastTime = time;

		this.ctx.clearRect(0, 0, this.canvas.width, this.canvas.height);

		this.drawTrail();

		this.drawPendulum(x1, x2, y1, y2);
		requestAnimationFrame(this.draw.bind(this));
	}

	drawPendulum(x1, x2, y1, y2) {
		this.ctx.lineWidth = 5;
		this.ctx.fillStyle = "#2196F3";
		this.ctx.strokeStyle = "#fff";

		this.ctx.beginPath();
		this.ctx.moveTo(this.hangPos.x, this.hangPos.y);
		this.ctx.lineTo(x1, y1);
		this.ctx.stroke();

		this.ctx.beginPath();
		this.ctx.moveTo(x1, y1);
		this.ctx.lineTo(x2, y2);
		this.ctx.stroke();

		this.ctx.lineWidth = 4;
		this.ctx.beginPath();
		this.ctx.arc(this.hangPos.x, this.hangPos.y, 10, 0, 2 * Math.PI);
		this.ctx.fill();
		this.ctx.stroke();

		this.ctx.beginPath();
		this.ctx.arc(x1, y1, 10, 0, 2 * Math.PI);
		this.ctx.fill();
		this.ctx.stroke();

		this.ctx.beginPath();
		this.ctx.arc(x2, y2, 10, 0, 2 * Math.PI);
		this.ctx.fill();
		this.ctx.stroke();
	}

	drawTrail() {
		this.ctx.shadowColor = "#2196F3";

		this.ctx.lineWidth = 5;
		this.ctx.lineJoin = "round";

		this.ctx.beginPath();
		let batchSize = this.batchSize;
		let i = 0;
		while (i < this.trail.length) {
			if (
				i + batchSize > this.trail.length ||
				this.trail.length < this.trailLength
			)
				batchSize = 1;

			const pointBatch = this.trail.slice(i + 1, i + 1 + batchSize);
			const previousPoint = this.trail[i];

			this.ctx.beginPath();
			this.ctx.moveTo(previousPoint.x, previousPoint.y);

			for (let point of pointBatch) {
				this.ctx.lineTo(point.x, point.y);
			}

			this.ctx.strokeStyle =
				"rgba(100,181,246, " +
				this.map(i + batchSize, 0, this.trail.length, 0, 1) +
				")";
			this.ctx.stroke();

			i += batchSize;
		}
	}

	map(number, in_min, in_max, out_min, out_max) {
		return (
			((number - in_min) * (out_max - out_min)) / (in_max - in_min) + out_min
		);
	}
}

new Visualization();

</script>

</body>
</html>